<?php

namespace OpenSoutheners\LaravelCompanionApps\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use OpenSoutheners\LaravelCompanionApps\Platform;
use OpenSoutheners\LaravelCompanionApps\Support\Facades\Companion;

class DoctorCommand extends Command
{
    protected $name = 'app:companion:doctor';

    protected $description = 'Check companion app and PWA configuration';

    protected Filesystem $filesystem;

    public function __construct()
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
        $failures = 0;

        $failures += $this->checkManifest();
        $failures += $this->checkServiceWorker();
        $failures += $this->checkNativeVerificationFiles();

        if ($failures > 0) {
            $this->error("Companion doctor found {$failures} issue(s).");

            return 1;
        }

        $this->info('Companion doctor found no issues.');

        return 0;
    }

    protected function checkManifest(): int
    {
        $failures = 0;

        if (config('companion.pwa.enabled', true) === false) {
            $this->components->info('PWA support is disabled.');

            return 0;
        }

        $required = ['name', 'short_name', 'start_url', 'display', 'icons'];

        foreach ($required as $field) {
            if (blank(config("companion.pwa.{$field}"))) {
                $this->components->error("Missing PWA config field: {$field}");
                $failures++;
            }
        }

        $manifestPath = $this->configString('companion.pwa.manifest_path', '/manifest.json');

        if (! $this->publicFileExists($manifestPath)) {
            $this->components->warn('Manifest file was not found in the configured public path.');
        } else {
            $this->components->info('Manifest file exists.');
            $failures += $this->checkManifestJson($manifestPath);
        }

        return $failures;
    }

    protected function checkServiceWorker(): int
    {
        if (config('companion.pwa.service_worker.enabled', false) === false) {
            $this->components->info('Service worker generation is disabled.');

            return 0;
        }

        if (! $this->publicFileExists($this->configString('companion.pwa.service_worker.path', '/service-worker.js'))) {
            $this->components->warn('Service worker file was not found in the configured public path.');

            return 0;
        }

        $this->components->info('Service worker file exists.');

        return 0;
    }

    protected function checkNativeVerificationFiles(): int
    {
        $failures = 0;

        $androidApps = Companion::listByPlatform(Platform::Android);
        if (count($androidApps) > 0) {
            $assetLinksPath = '/.well-known/assetlinks.json';

            if (! $this->publicFileExists($assetLinksPath)) {
                $this->components->error('Android apps are configured, but assetlinks.json was not found.');
                $failures++;
            } elseif (! $this->assetLinksContainsApps(array_keys($androidApps), $assetLinksPath)) {
                $this->components->error('Android apps are configured, but assetlinks.json does not include all registered packages.');
                $failures++;
            } else {
                $this->components->info('Android app links verification file looks good.');
            }
        }

        $appleApps = Companion::listByPlatform(Platform::Apple);
        if (count($appleApps) > 0) {
            $associationPath = '/.well-known/apple-app-site-association';

            if (! $this->publicFileExists($associationPath)) {
                $this->components->error('Apple apps are configured, but apple-app-site-association was not found.');
                $failures++;
            } elseif (! $this->appleAssociationContainsApps(array_keys($appleApps), $associationPath)) {
                $this->components->error('Apple apps are configured, but apple-app-site-association does not include all registered app IDs.');
                $failures++;
            } else {
                $this->components->info('Apple universal links verification file looks good.');
            }
        }

        return $failures;
    }

    protected function publicFileExists(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return $this->filesystem->exists($this->publicPath($path));
    }

    protected function publicPath(string $path): string
    {
        return trim($this->configString('companion.files.base_path', 'public').'/'.ltrim($path, '/'), '/');
    }

    protected function checkManifestJson(string $path): int
    {
        $contents = $this->decodeJsonFile($path);

        if (! is_array($contents)) {
            $this->components->error('Manifest file could not be decoded as JSON.');

            return 1;
        }

        if (! array_key_exists('prefer_related_applications', $contents)) {
            $this->components->error('Manifest file is missing prefer_related_applications.');

            return 1;
        }

        $registeredApps = Companion::list();
        if (count($registeredApps) === 0) {
            return 0;
        }

        $relatedApplications = $contents['related_applications'] ?? null;
        if (! is_array($relatedApplications)) {
            $this->components->error('Manifest file is missing related_applications for registered companion apps.');

            return 1;
        }

        if (count($relatedApplications) < count($registeredApps)) {
            $this->components->error('Manifest file related_applications does not include all registered companion apps.');

            return 1;
        }

        return 0;
    }

    /**
     * @param array<int, string> $registeredApps
     */
    protected function assetLinksContainsApps(array $registeredApps, string $path): bool
    {
        $contents = $this->decodeJsonFile($path);

        if (! is_array($contents)) {
            return false;
        }

        $packageNames = array_map(function (array $item): ?string {
            $target = $item['target'] ?? null;

            if (! is_array($target)) {
                return null;
            }

            $packageName = $target['package_name'] ?? null;

            return is_string($packageName) ? $packageName : null;
        }, array_filter($contents, 'is_array'));

        return count(array_diff($registeredApps, array_filter($packageNames))) === 0;
    }

    /**
     * @param array<int, string> $registeredApps
     */
    protected function appleAssociationContainsApps(array $registeredApps, string $path): bool
    {
        $contents = $this->decodeJsonFile($path);
        $applinks = is_array($contents) ? ($contents['applinks'] ?? null) : null;
        $details = is_array($applinks) ? ($applinks['details'] ?? null) : null;

        if (! is_array($details)) {
            return false;
        }

        $appIds = array_map(function (array $item): ?string {
            $appId = $item['appID'] ?? null;

            return is_string($appId) ? $appId : null;
        }, array_filter($details, 'is_array'));

        return count(array_diff($registeredApps, array_filter($appIds))) === 0;
    }

    /**
     * @return array<mixed>|null
     */
    protected function decodeJsonFile(string $path): ?array
    {
        $contents = $this->filesystem->get($this->publicPath($path));

        if (! is_string($contents)) {
            return null;
        }

        /** @var mixed $decoded */
        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function configString(string $key, string $default): string
    {
        $value = config($key, $default);

        return is_scalar($value) ? (string) $value : $default;
    }
}
