<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use OpenSoutheners\LaravelCompanionApps\CompanionApplication;
use OpenSoutheners\LaravelCompanionApps\Platform;
use OpenSoutheners\LaravelCompanionApps\ServiceProvider;

class DoctorCommandTest extends TestCase
{
    protected string $doctorBasePath = 'tests/workbench/doctor';

    protected function setUp(): void
    {
        parent::setUp();

        config(['companion.files.base_path' => $this->doctorBasePath]);

        app(Filesystem::class)->deleteDirectory(base_path($this->doctorBasePath));
    }

    protected function tearDown(): void
    {
        app(Filesystem::class)->deleteDirectory(base_path($this->doctorBasePath));

        parent::tearDown();
    }

    public function test_doctor_command_passes_with_default_configuration()
    {
        $command = $this->artisan('app:companion:doctor');

        $command->expectsOutputToContain('Companion doctor found no issues.');

        $this->assertEquals(0, $command->run());
    }

    public function test_doctor_command_fails_when_required_pwa_config_is_missing()
    {
        config(['companion.pwa.name' => null]);

        $command = $this->artisan('app:companion:doctor');

        $command->expectsOutputToContain('Missing PWA config field: name');
        $command->expectsOutputToContain('Companion doctor found 1 issue(s).');

        $this->assertEquals(1, $command->run());
    }

    public function test_doctor_command_passes_for_registered_apps_with_expected_generated_files()
    {
        ServiceProvider::loadApplications([
            CompanionApplication::make('com.example.android', Platform::Android),
            CompanionApplication::make('com.example.apple', Platform::Apple)->setStoreOptions('123456789', 'example-app'),
        ]);

        $this->writeJsonFile('/.well-known/assetlinks.json', [
            [
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => 'com.example.android',
                    'sha256_cert_fingerprints' => ['ab:cd:ef'],
                ],
            ],
        ]);

        $this->writeJsonFile('/.well-known/apple-app-site-association', [
            'applinks' => [
                'apps' => ['com.example.apple'],
                'details' => [
                    [
                        'appID' => 'com.example.apple',
                        'paths' => ['/foo/*'],
                    ],
                ],
            ],
        ]);

        $this->writeJsonFile('/manifest.json', [
            'prefer_related_applications' => false,
            'related_applications' => [
                [
                    'platform' => 'play',
                    'url' => 'https://play.google.com/store/apps/details?id=com.example.android',
                    'id' => 'com.example.android',
                ],
                [
                    'platform' => 'itunes',
                    'url' => 'https://apps.apple.com/en/app/example-app/id123456789',
                ],
            ],
        ]);

        $exitCode = Artisan::call('app:companion:doctor');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Android app links verification file looks good.', $output);
        $this->assertStringContainsString('Apple universal links verification file looks good.', $output);
        $this->assertStringContainsString('Manifest file exists.', $output);
        $this->assertStringContainsString('Companion doctor found no issues.', $output);
    }

    public function test_doctor_command_fails_when_registered_apps_are_missing_generated_files()
    {
        ServiceProvider::loadApplications([
            CompanionApplication::make('com.example.android', Platform::Android),
            CompanionApplication::make('com.example.apple', Platform::Apple),
        ]);

        $exitCode = Artisan::call('app:companion:doctor');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Android apps are configured, but assetlinks.json was not found.', $output);
        $this->assertStringContainsString('Apple apps are configured, but apple-app-site-association was not found.', $output);
        $this->assertStringContainsString('Manifest file was not found in the configured public path.', $output);
        $this->assertStringContainsString('Companion doctor found 2 issue(s).', $output);
    }

    /**
     * @param array<mixed> $contents
     */
    protected function writeJsonFile(string $relativePath, array $contents): void
    {
        $filesystem = app(Filesystem::class);
        $fullPath = base_path(trim($this->doctorBasePath.'/'.ltrim($relativePath, '/'), '/'));

        $filesystem->ensureDirectoryExists(dirname($fullPath));
        $filesystem->put($fullPath, json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
