<?php

namespace OpenSoutheners\LaravelCompanionApps\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use OpenSoutheners\LaravelCompanionApps\Generators\AppleAppSiteAssociationGenerator;
use OpenSoutheners\LaravelCompanionApps\Generators\AssetLinksGenerator;
use OpenSoutheners\LaravelCompanionApps\Generators\ManifestGenerator;
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
     * @var string|null
     */
    protected $description = 'Generate companion applications links verification files';

    /**
     * @var array<string, array>
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
        if (count(Companion::list()) === 0) {
            $this->warn('No apps registered, please register one first before running this command.');

            return 1;
        }

        $this->assetLinksFile();
        $this->appleAppSiteAssociationFile();
        $hasManifest = $this->webManifestFile();
        $this->createFiles();

        $this->info(sprintf('App links%s files sucessfully written!', $hasManifest ? ' and web manifest' : ''));

        return 0;
    }

    protected function createFiles(): void
    {
        foreach ($this->files as $filePath => $fileContent) {
            $jsonFileContent = json_encode($fileContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if (! $jsonFileContent) {
                continue;
            }

            $this->outputComponents()->task($filePath, function () use ($filePath, $jsonFileContent) {
                $this->filesystem->makeDirectory(Str::beforeLast($filePath, '/'));
    
                $this->filesystem->put($filePath, $jsonFileContent);

                $this->outputComponents()->info('File created successfully');
            });
        }
    }

    protected function webManifestFile(): bool
    {
        if ($this->option('no-manifest') || ! confirm('Do you want to generate a manifest for your Laravel app?')) {
            return false;
        }

        $manifestOptions = $this->option('manifest-options') ?? '';

        $generator = new ManifestGenerator;

        $manifestArr = $generator
            ->preferRelatedApplications(str_contains($manifestOptions, 'related_apps'))
            ->generate();

        $this->files[config('companion.files.base_path', 'public').'/manifest.json'] = $manifestArr;

        return true;
    }

    protected function assetLinksFile(): void
    {
        $apps = [];
        $fingerprints = [];

        foreach (Companion::listByPlatform(Platform::Android) as $app) {
            $fingerprints[$app->getName()] = explode(',', text(
                label: "Introduce comma separated list of SHA2 fingerprints for your Android app ({$app->getName()})",
                required: true
            ));

            $apps[] = $app;
        }

        $generator = new AssetLinksGenerator($apps, $fingerprints);

        $this->files[config('companion.files.base_path', 'public').'/.well-known/assetlinks.json'] = $generator->generate();
    }

    /**
     * Generate AASA file to verify app links into Apple.
     */
    protected function appleAppSiteAssociationFile(): void
    {
        $apps = [];

        foreach (Companion::listByPlatform(Platform::Apple) as $app) {
            $apps[$app->getName()] = explode(',', text(
                label: "Comma separated associated paths to site for Apple's application ({$app->getName()})",
                required: true
            ));
        }

        $generator = new AppleAppSiteAssociationGenerator($apps);

        $this->files[config('companion.files.base_path', 'public').'/.well-known/apple-app-site-association'] = $generator->generate();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['no-manifest', '', InputOption::VALUE_NONE, 'Do not generate manifest.json for your web application'],
            ['manifest-options', '', InputOption::VALUE_OPTIONAL, 'Comma separated list of options for the generation of your manifest'],
        ];
    }
}
