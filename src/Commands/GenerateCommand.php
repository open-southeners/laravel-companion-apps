<?php

namespace OpenSoutheners\LaravelCompanionApps\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use OpenSoutheners\LaravelCompanionApps\Generators\AppleAppSiteAssociationGenerator;
use OpenSoutheners\LaravelCompanionApps\Generators\AssetLinksGenerator;
use OpenSoutheners\LaravelCompanionApps\Generators\ManifestGenerator;
use OpenSoutheners\LaravelCompanionApps\Generators\ServiceWorkerGenerator;
use OpenSoutheners\LaravelCompanionApps\Support\Facades\Companion;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use OpenSoutheners\LaravelCompanionApps\Platform;
use Symfony\Component\Console\Input\InputOption;

class GenerateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'app:companion:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate companion applications links verification files';

    /**
     * @var array<string, array<mixed>|string>
     */
    protected array $files = [];

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem $filesystem
     */
    public function __construct(protected Filesystem $filesystem)
    {
        parent::__construct();

        /** @var string|null $disk */
        $disk = config('companion.files.disk');
        $filesManager = app(FilesystemManager::class);

        $this->filesystem = $disk
            ? $filesManager->disk($disk)
            : $filesManager->createLocalDriver([
                'root' => base_path(),
                'throw' => true,
            ]);
    }

    public function handle(): int
    {
        $hasAndroidApps = count(Companion::listByPlatform(Platform::Android)) > 0;
        $hasAppleApps = count(Companion::listByPlatform(Platform::Apple)) > 0;

        if (! $hasAndroidApps && ! $hasAppleApps && $this->option('no-manifest') && ! $this->option('service-worker')) {
            $this->warn('No apps registered and no PWA files requested.');

            return 1;
        }

        if ($hasAndroidApps) {
            $this->assetLinksFile();
        }

        if ($hasAppleApps) {
            $this->appleAppSiteAssociationFile();
        }

        $hasManifest = $this->webManifestFile();
        $hasServiceWorker = $this->serviceWorkerFile();

        if ($this->files === []) {
            $this->warn('No companion app files were generated.');

            return 1;
        }

        $this->createFiles();

        $generatedFiles = array_filter([
            ($hasAndroidApps || $hasAppleApps) ? 'app links' : null,
            $hasManifest ? 'web manifest' : null,
            $hasServiceWorker ? 'service worker' : null,
        ]);

        $this->info(sprintf('%s files successfully written!', ucfirst($this->humanizeGeneratedFiles($generatedFiles))));

        return 0;
    }

    protected function createFiles(): void
    {
        foreach ($this->files as $filePath => $fileContent) {
            $content = is_string($fileContent)
                ? $fileContent
                : json_encode($fileContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if (! $content) {
                continue;
            }

            $this->outputComponents()->task($filePath, function () use ($filePath, $content) {
                $directory = Str::beforeLast($filePath, '/');

                if ($directory !== '') {
                    $this->filesystem->makeDirectory($directory);
                }
    
                $this->filesystem->put($filePath, $content);

                $this->outputComponents()->info('File created successfully');
            });
        }
    }

    /**
     * @param  array<int, string>  $generatedFiles
     */
    protected function humanizeGeneratedFiles(array $generatedFiles): string
    {
        $count = count($generatedFiles);

        if ($count <= 1) {
            return $generatedFiles[0] ?? 'Files';
        }

        if ($count === 2) {
            return implode(' and ', $generatedFiles);
        }

        $lastFile = array_pop($generatedFiles);

        return implode(', ', $generatedFiles).' and '.$lastFile;
    }

    protected function webManifestFile(): bool
    {
        if ($this->option('no-manifest') || ! confirm('Do you want to generate a manifest for your Laravel app?')) {
            return false;
        }

        $manifestOptions = $this->optionString('manifest-options');

        $generator = new ManifestGenerator;

        $manifestArr = $generator
            ->preferRelatedApplications(str_contains($manifestOptions, 'related_apps'))
            ->generate();

        $this->files[$this->basePath().'/manifest.json'] = $manifestArr;

        return true;
    }

    protected function serviceWorkerFile(): bool
    {
        if (! $this->option('service-worker')) {
            return false;
        }

        $generator = new ServiceWorkerGenerator;

        $serviceWorkerPath = $this->configString('companion.pwa.service_worker.path', '/service-worker.js');

        $this->files[$this->basePath().'/'.ltrim($serviceWorkerPath, '/')] = $generator->generate();

        return true;
    }

    protected function assetLinksFile(): void
    {
        /** @var array<string, \OpenSoutheners\LaravelCompanionApps\CompanionApplication> $apps */
        $apps = Companion::listByPlatform(Platform::Android);
        $fingerprints = [];

        foreach ($apps as $name => $app) {
            $fingerprints[$name] = explode(',', text(
                label: "Introduce comma separated list of SHA2 fingerprints for your Android app ({$name})",
                required: true
            ));
        }

        $generator = new AssetLinksGenerator($apps, $fingerprints);

        $this->files[$this->basePath().'/.well-known/assetlinks.json'] = $generator->generate();
    }

    /**
     * Generate AASA file to verify app links into Apple.
     */
    protected function appleAppSiteAssociationFile(): void
    {
        $apps = Companion::listByPlatform(Platform::Apple);
        /** @var array<string, list<string>> $appPathsArr */
        $appPathsArr = [];

        foreach ($apps as $name => $app) {
            $appPathsArr[(string) $name] = explode(',', text(
                label: "Comma separated associated paths to site for Apple's application ({$name})",
                required: true
            ));
        }

        $generator = new AppleAppSiteAssociationGenerator($appPathsArr);

        $this->files[$this->basePath().'/.well-known/apple-app-site-association'] = $generator->generate();
    }

    protected function basePath(): string
    {
        return $this->configString('companion.files.base_path', 'public');
    }

    protected function optionString(string $key, string $default = ''): string
    {
        $value = $this->option($key);

        return is_scalar($value) ? (string) $value : $default;
    }

    protected function configString(string $key, string $default): string
    {
        $value = config($key, $default);

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * Get the console command options.
     *
     * @return array<int, array<int, int|string>>
     */
    public function getOptions(): array
    {
        return [
            ['no-manifest', '', InputOption::VALUE_NONE, 'Do not generate manifest.json for your web application'],
            ['manifest-options', '', InputOption::VALUE_OPTIONAL, 'Comma separated list of options for the generation of your manifest'],
            ['service-worker', '', InputOption::VALUE_NONE, 'Generate a service worker for your web application'],
        ];
    }
}
