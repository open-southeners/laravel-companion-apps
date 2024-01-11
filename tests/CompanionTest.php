<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use Illuminate\Support\Facades\Route;
use OpenSoutheners\LaravelCompanionApps\Companion;
use OpenSoutheners\LaravelCompanionApps\Manager;
use OpenSoutheners\LaravelCompanionApps\Platform;

class CompanionTest extends TestCase
{
    public function test_companion_supports_links()
    {
        $this->assertFalse(Companion::make('com.example', Platform::Android)->supportsLinks());
        $this->assertTrue(Companion::make('com.example', Platform::Android)->linkScheme('test')->supportsLinks());
    }

    public function test_companion_app_link_redirects()
    {
        Manager::register(Companion::make('com.example', Platform::Android)->linkScheme('example'));

        Route::get('/', function () {
            return Companion::android('com.example')->link('foo/bar');
        });

        $this->get('/')->assertRedirect((string) Companion::android('com.example')->link('foo/bar'));
    }

    public function test_companion_app_link_redirects_mixin_works_same_way()
    {
        Manager::register(Companion::make('com.example', Platform::Android)->linkScheme('example'));

        Route::get('/', function () {
            return redirect()->toApp(Companion::android('com.example'), 'foo/bar');
        });

        $this->get('/')->assertRedirect((string) Companion::android('com.example')->link('foo/bar'));
    }

    public function test_companion_app_link_redirects_with_fallback()
    {
        Manager::register(Companion::make('com.example', Platform::Android)->linkScheme('example'));

        Route::get('/', function () {
            return Companion::android('com.example')->link('foo/bar')->fallbackUrl('https://google.com');
        });

        $this->get('/')->assertRedirect((string) Companion::android('com.example')->link('foo/bar')->fallbackUrl('https://google.com'));
    }

    public function test_companion_app_get_store_platform()
    {
        $this->assertEquals('play', Companion::make('com.example', Platform::Android)->getPlatformStore());
        $this->assertEquals('itunes', Companion::make('com.example', Platform::Apple)->getPlatformStore());
    }

    public function test_companion_app_get_store_link()
    {
        $this->assertEquals(
            'https://play.google.com/store/apps/details?id=com.example',
            Companion::make('com.example', Platform::Android)->getStoreLink()
        );
    }

    public function test_companion_app_get_store_meta_tag()
    {
        $this->assertEquals(
            '<meta name="google-play-app" content="app-id=com.example">',
            Companion::make('com.example', Platform::Android)->getStoreLinkMetaTag()
        );
    }

    public function test_companion_app_get_store_badge_html()
    {
        $this->assertEquals(
            '<a target="_blank" href="https://play.google.com/store/apps/details?id=com.example"><img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" width="180" alt="" /></a>',
            Companion::make('com.example', Platform::Android)->getStoreBadgeHtml()
        );
    }

    public function test_companion_app_get_store_badge_html_with_different_region()
    {
        
        $this->assertEquals(
            '<a target="_blank" href="https://play.google.com/store/apps/details?id=com.example"><img src="https://play.google.com/intl/en_us/badges/static/images/badges/es_badge_web_generic.png" width="180" alt="" /></a>',
            Companion::make('com.example', Platform::Android)->setStoreOptions(region: 'es')->getStoreBadgeHtml()
        );
    }

    public function test_companion_app_get_store_badge_html_accepts_optional_args_as_badge_img_attributes()
    {
        $this->assertEquals(
            '<a target="_blank" href="https://play.google.com/store/apps/details?id=com.example"><img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" width="240" alt="download_my_app" /></a>',
            Companion::make('com.example', Platform::Android)->getStoreBadgeHtml(width: 240, alt: 'download_my_app')
        );
    }
}
