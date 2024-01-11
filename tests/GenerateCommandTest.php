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
        'public/.well-known/apple_app_site_association',
        'public/manifest.json',
    ];

    public function test_generate_command_generates_nothing_when_no_apps_registered_found()
    {
        $command = $this->artisan('app:companion:generate');

        $command->expectsOutput('No apps registered, please register one first before running this command.');

        $exitCode = $command->run();

        $this->assertEquals(1, $exitCode);

        $this->assertFileDoesNotExist($this->files[0]);
        $this->assertFileDoesNotExist($this->files[1]);
        $this->assertFileDoesNotExist($this->files[2]);
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

        $command->expectsOutput('App links files sucessfully written!');

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

        $command->expectsOutput('App links and web manifest files sucessfully written!');

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
            '"prefer_related_applications": false,',
            '"related_applications": [',
            '"platform": "play",',
            '"url": "https://play.google.com/store/apps/details?id=com.example",',
            '"id": "com.example"',
            '"platform": "itunes",',
            '"url": "https://apps.apple.com/en/app/example-app/id123456789"',
        ], 'public/manifest.json');
    }
}
