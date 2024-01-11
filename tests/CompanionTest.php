<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use OpenSoutheners\LaravelCompanionApps\CompanionApplication;
use OpenSoutheners\LaravelCompanionApps\Exceptions\CompanionAppNotFound;
use OpenSoutheners\LaravelCompanionApps\Platform;
use OpenSoutheners\LaravelCompanionApps\ServiceProvider;
use OpenSoutheners\LaravelCompanionApps\Support\Facades\Companion;

class CompanionTest extends TestCase
{
    public function test_register_app_adds_to_platform_array()
    {
        ServiceProvider::loadApplications([CompanionApplication::make('com.example', Platform::Android)]);
        
        $this->assertCount(1, Companion::list());
        $this->assertInstanceOf(CompanionApplication::class, Companion::android('com.example'));
        
        ServiceProvider::loadApplications([CompanionApplication::make('com.example', Platform::Apple)]);
        
        $this->assertCount(2, Companion::list());
        $this->assertInstanceOf(CompanionApplication::class, Companion::apple('com.example'));
        
        ServiceProvider::loadApplications([CompanionApplication::make('com.example', Platform::Web)]);
        
        $this->assertCount(3, Companion::list());
        $this->assertInstanceOf(CompanionApplication::class, Companion::web('com.example'));
    }

    public function test_applications_by_platform_returns_array()
    {
        $androidApp = CompanionApplication::make('com.example', Platform::Android);
        $appleApp = CompanionApplication::make('com.example', Platform::Apple);
        $appleDevApp = CompanionApplication::make('com.example_dev', Platform::Apple);
        
        ServiceProvider::loadApplications([$androidApp, $appleApp, $appleDevApp]);

        $this->assertIsArray(Companion::listByPlatform(Platform::Android));
        $this->assertCount(1, Companion::listByPlatform(Platform::Android));
        $this->assertIsArray(Companion::listByPlatform(Platform::Apple));
        $this->assertCount(2, Companion::listByPlatform(Platform::Apple));
    }

    public function test_get_application_by_name_when_none_exists_throws_exception()
    {
        $this->expectException(CompanionAppNotFound::class);

        Companion::getByName(Platform::Android, 'com.example');
    }

    public function test_companion_apps_meta_tags_gets_string_of_html_meta_tags()
    {
        $androidApp = CompanionApplication::make('com.example', Platform::Android);
        $appleApp = CompanionApplication::make('com.example', Platform::Apple);
        
        ServiceProvider::loadApplications([$androidApp, $appleApp]);
        
        $metaTags = Companion::metaTags();

        $this->assertIsString($metaTags);
        $this->assertStringContainsString(
            Companion::android('com.example')->getStoreLinkMetaTag(),
            $metaTags
        );
    }
}
