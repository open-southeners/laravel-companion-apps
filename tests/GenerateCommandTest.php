<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use OpenSoutheners\LaravelCompanionApps\CompanionApplication;
use OpenSoutheners\LaravelCompanionApps\Platform;
use OpenSoutheners\LaravelCompanionApps\ServiceProvider;
use Orchestra\Testbench\Concerns\InteractsWithPublishedFiles;

class GenerateCommandTest extends TestCase
{
    use InteractsWithPublishedFiles;

    protected $files = [
        'public/.well-known/assetlinks.json',
        'public/.well-known/apple-app-site-association',
        'public/manifest.json',
        'public/service-worker.js',
    ];

    public function test_generate_command_generates_nothing_when_no_apps_registered_found()
    {
        $command = $this->artisan('app:companion:generate', ['--no-manifest' => true]);

        $command->expectsOutput('No apps registered and no PWA files requested.');

        $exitCode = $command->run();

        $this->assertEquals(1, $exitCode);

        $this->assertFileDoesNotExist($this->files[0]);
        $this->assertFileDoesNotExist($this->files[1]);
        $this->assertFileDoesNotExist($this->files[2]);
        $this->assertFileDoesNotExist($this->files[3]);
    }

    public function test_generate_command_with_no_manifest_option_does_not_generate_manifest_file()
    {
        $androidApp = CompanionApplication::make('com.example', Platform::Android);
        $androidDevApp = CompanionApplication::make('com.example_dev', Platform::Android);
        $appleApp = CompanionApplication::make('com.example', Platform::Apple)->setStoreOptions('123456789', 'example-app');
        $appleDevApp = CompanionApplication::make('com.example_dev', Platform::Apple);

        ServiceProvider::loadApplications([
            $androidApp, $androidDevApp, $appleApp, $appleDevApp,
        ]);

        $command = $this->artisan('app:companion:generate', ['--no-manifest' => true]);

        $command->expectsQuestion('Introduce comma separated list of SHA2 fingerprints for your Android app (com.example)', 'ab:cd:ef');
        $command->expectsQuestion('Introduce comma separated list of SHA2 fingerprints for your Android app (com.example_dev)', 'aa:bb:cc');
        $command->expectsQuestion('Comma separated associated paths to site for Apple\'s application (com.example)', '/foo/*');
        $command->expectsQuestion('Comma separated associated paths to site for Apple\'s application (com.example_dev)', '/foo/*');

        $command->expectsOutputToContain('App links files successfully written!');

        $exitCode = $command->run();

        $this->assertEquals(0, $exitCode);

        $this->assertFileContains([
            '"relation": [',
            '"delegate_permission/common.handle_all_urls"',
            '"target": {',
            '"namespace": "android_app",',
            '"package_name": "com.example",',
            '"sha256_cert_fingerprints": [',
            '"ab:cd:ef"',
        ], 'public/.well-known/assetlinks.json');
        
        $this->assertFileContains([
            '"applinks": {',
            '"apps": [',
            '"com.example",',
            '"com.example_dev"',
            '"apps": [',
            '"details": [',
            '"appID": "com.example",',
            '"paths": [',
            '"/foo/*"',
        ], 'public/.well-known/apple-app-site-association');

        $this->assertFileDoesNotExist($this->files[2]);
    }

    public function test_generate_command_can_generate_manifest_without_native_apps()
    {
        $command = $this->artisan('app:companion:generate');

        $command->expectsConfirmation('Do you want to generate a manifest for your Laravel app?', 'yes');
        $exitCode = $command->run();

        $this->assertEquals(0, $exitCode);

        $this->assertFileContains([
            '"name": "Laravel",',
            '"related_applications": []',
        ], 'public/manifest.json');
    }

    public function test_generate_command_generates_all_three_files()
    {
        $androidApp = CompanionApplication::make('com.example', Platform::Android);
        $androidDevApp = CompanionApplication::make('com.example_dev', Platform::Android);
        $appleApp = CompanionApplication::make('com.example', Platform::Apple)->setStoreOptions('123456789', 'example-app');
        $appleDevApp = CompanionApplication::make('com.example_dev', Platform::Apple);

        ServiceProvider::loadApplications([
            $androidApp, $androidDevApp, $appleApp, $appleDevApp,
        ]);

        $command = $this->artisan('app:companion:generate');

        $command->expectsQuestion('Introduce comma separated list of SHA2 fingerprints for your Android app (com.example)', 'ab:cd:ef');
        $command->expectsQuestion('Introduce comma separated list of SHA2 fingerprints for your Android app (com.example_dev)', 'aa:bb:cc');
        $command->expectsQuestion('Comma separated associated paths to site for Apple\'s application (com.example)', '/foo/*');
        $command->expectsQuestion('Comma separated associated paths to site for Apple\'s application (com.example_dev)', '/foo/*');
        $command->expectsConfirmation('Do you want to generate a manifest for your Laravel app?', 'yes');

        $command->expectsOutputToContain('App links and web manifest files successfully written!');

        $exitCode = $command->run();

        $this->assertEquals(0, $exitCode);

        $this->assertFileContains([
            '"relation": [',
            '"delegate_permission/common.handle_all_urls"',
            '"target": {',
            '"namespace": "android_app",',
            '"package_name": "com.example",',
            '"sha256_cert_fingerprints": [',
            '"ab:cd:ef"',
        ], 'public/.well-known/assetlinks.json');
        
        $this->assertFileContains([
            '"applinks": {',
            '"apps": [',
            '"com.example",',
            '"com.example_dev"',
            '"apps": [',
            '"details": [',
            '"appID": "com.example",',
            '"paths": [',
            '"/foo/*"',
        ], 'public/.well-known/apple-app-site-association');

        $this->assertFileContains([
            '"start_url": "/",',
            '"scope": "/",',
            '"display": "standalone",',
            '"theme_color": "#111827",',
            '"background_color": "#ffffff",',
            '"prefer_related_applications": false,',
            '"related_applications": [',
            '"platform": "play",',
            '"url": "https://play.google.com/store/apps/details?id=com.example",',
            '"id": "com.example"',
            '"platform": "itunes",',
            '"url": "https://apps.apple.com/en/app/example-app/id123456789"',
        ], 'public/manifest.json');
    }

    public function test_generate_command_generates_service_worker_file()
    {
        $androidApp = CompanionApplication::make('com.example', Platform::Android);
        $appleApp = CompanionApplication::make('com.example', Platform::Apple);

        ServiceProvider::loadApplications([$androidApp, $appleApp]);

        $command = $this->artisan('app:companion:generate', [
            '--no-manifest' => true,
            '--service-worker' => true,
        ]);

        $command->expectsQuestion('Introduce comma separated list of SHA2 fingerprints for your Android app (com.example)', 'ab:cd:ef');
        $command->expectsQuestion('Comma separated associated paths to site for Apple\'s application (com.example)', '/foo/*');
        $command->expectsOutputToContain('App links and service worker files successfully written!');

        $exitCode = $command->run();

        $this->assertEquals(0, $exitCode);

        $this->assertFileContains([
            'const CACHE_NAME = "companion-app-v1";',
            'const OFFLINE_URL = "/offline";',
            'Promise.allSettled(',
            "request.mode === 'navigate'",
            'caches.match(OFFLINE_URL)',
            'function shouldCacheResponse(response) {',
        ], 'public/service-worker.js');
    }
}
