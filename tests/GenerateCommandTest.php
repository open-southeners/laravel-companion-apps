<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use OpenSoutheners\LaravelCompanionApps\Companion;
use OpenSoutheners\LaravelCompanionApps\Manager;
use OpenSoutheners\LaravelCompanionApps\Platform;
use Orchestra\Testbench\Concerns\InteractsWithPublishedFiles;

class GenerateCommandTest extends TestCase
{
    use InteractsWithPublishedFiles;

    protected $files = [
        'public/.well-known/assetlinks.json',
        'public/.well-known/apple_app_site_association',
        'public/manifest.json',
    ];

    public function test_generate_command_generates_all_three_files()
    {
        Manager::register($androidApp = Companion::make('com.example', Platform::Android));
        Manager::register($androidDevApp = Companion::make('com.example_dev', Platform::Android));
        Manager::register($appleApp = Companion::make('com.example', Platform::Apple)->setStoreOptions('123456789', 'example-app'));
        Manager::register($appleDevApp = Companion::make('com.example_dev', Platform::Apple));

        $command = $this->artisan('app:companion:generate');

        $command->expectsQuestion('Introduce comma separated list of SHA2 fingerprints for your Android app (com.example)', 'ab:cd:ef');
        $command->expectsQuestion('Introduce comma separated list of SHA2 fingerprints for your Android app (com.example_dev)', 'aa:bb:cc');
        $command->expectsQuestion('Associated paths to site for Apple\'s application (com.example)', '/foo/*');
        $command->expectsQuestion('Associated paths to site for Apple\'s application (com.example_dev)', '/foo/*');
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
