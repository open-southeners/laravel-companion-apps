<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use OpenSoutheners\LaravelCompanionApps\CompanionApplication;
use OpenSoutheners\LaravelCompanionApps\Generators\ManifestGenerator;
use OpenSoutheners\LaravelCompanionApps\Platform;
use OpenSoutheners\LaravelCompanionApps\ServiceProvider;

class ManifestGeneratorTest extends TestCase
{
    public function test_manifest_generator_uses_structured_pwa_config()
    {
        config([
            'companion.pwa.manifest' => [
                'name' => 'Example App',
                'short_name' => 'Example',
                'description' => 'Installable example application',
                'id' => '/app',
                'start_url' => '/app',
                'scope' => '/',
                'display' => 'standalone',
                'orientation' => 'portrait',
                'theme_color' => '#123456',
                'background_color' => '#ffffff',
                'lang' => 'en',
                'dir' => 'ltr',
                'categories' => ['productivity'],
                'icons' => [
                    [
                        'src' => '/icons/icon-192.png',
                        'sizes' => '192x192',
                        'type' => 'image/png',
                    ],
                ],
            ],
        ]);

        ServiceProvider::loadApplications([
            CompanionApplication::make('com.example', Platform::Android),
            CompanionApplication::make('com.example', Platform::Apple)->setStoreOptions('123456789', 'example-app'),
        ]);

        $manifest = (new ManifestGenerator)->generate();

        $this->assertSame('Example App', $manifest['name']);
        $this->assertSame('Example', $manifest['short_name']);
        $this->assertSame('/app', $manifest['id']);
        $this->assertSame('/app', $manifest['start_url']);
        $this->assertSame('portrait', $manifest['orientation']);
        $this->assertSame('#123456', $manifest['theme_color']);
        $this->assertSame(['productivity'], $manifest['categories']);
        $this->assertSame('/icons/icon-192.png', $manifest['icons'][0]['src']);
        $this->assertFalse($manifest['prefer_related_applications']);
        $this->assertSame('play', $manifest['related_applications'][0]['platform']);
        $this->assertSame('com.example', $manifest['related_applications'][0]['id']);
        $this->assertSame('itunes', $manifest['related_applications'][1]['platform']);
    }

    public function test_companion_pwa_blade_directive_is_registered()
    {
        $compiled = app('blade.compiler')->compileString('@companionPwaTags');

        $this->assertStringContainsString("app('companion')->pwaTags()", $compiled);
    }
}
