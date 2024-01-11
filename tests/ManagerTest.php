<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use OpenSoutheners\LaravelCompanionApps\Companion;
use OpenSoutheners\LaravelCompanionApps\Exceptions\CompanionAppNotFound;
use OpenSoutheners\LaravelCompanionApps\Manager;
use OpenSoutheners\LaravelCompanionApps\Platform;

class ManagerTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function test_manager_register_app_adds_to_platform_array()
    {
        Manager::register(Companion::make('com.example', Platform::Android));
        
        $this->assertCount(1, Manager::apps());
        $this->assertInstanceOf(Companion::class, Companion::android('com.example'));
        
        Manager::register(Companion::make('com.example', Platform::Apple));
        
        $this->assertCount(2, Manager::apps());
        $this->assertInstanceOf(Companion::class, Companion::apple('com.example'));
        
        Manager::register(Companion::make('com.example', Platform::Web));
        
        $this->assertCount(3, Manager::apps());
        $this->assertInstanceOf(Companion::class, Companion::web('com.example'));
    }

    /**
     * @runInSeparateProcess
     */
    public function test_manager_applications_by_platform_returns_array_or_instance_when_name_provided()
    {
        $androidApp = Companion::make('com.example', Platform::Android);
        $appleApp = Companion::make('com.example', Platform::Apple);
        $appleDevApp = Companion::make('com.example_dev', Platform::Apple);
        
        Manager::register($androidApp);
        Manager::register($appleApp);
        Manager::register($appleDevApp);

        $this->assertCount(1, Manager::applicationsByPlatform(Platform::Android));
        $this->assertCount(2, Manager::applicationsByPlatform(Platform::Apple));

        $this->assertEquals($androidApp, Manager::applicationsByPlatform(Platform::Android, 'com.example'));
        $this->assertEquals($appleApp, Manager::applicationsByPlatform(Platform::Apple, 'com.example'));
        $this->assertEquals($appleDevApp, Manager::applicationsByPlatform(Platform::Apple, 'com.example_dev'));
    }

    public function test_manager_applications_by_platform_with_name_when_none_exists_throws_exception()
    {
        $this->expectException(CompanionAppNotFound::class);
        
        Manager::applicationsByPlatform(Platform::Android, 'com.this_does_not_exist');
    }

    public function test_manager_companion_apps_meta_tags_gets_string_of_html_meta_tags()
    {
        $androidApp = Companion::make('com.example', Platform::Android);
        $appleApp = Companion::make('com.example', Platform::Apple);
        
        Manager::register($androidApp);
        Manager::register($appleApp);
        
        $metaTags = Manager::companionAppsHeader();

        $this->assertIsString($metaTags);
        $this->assertStringContainsString(
            Companion::android('com.example')->getStoreLinkMetaTag(),
            $metaTags
        );
    }
}
