<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use Illuminate\Support\Facades\Route;
use OpenSoutheners\LaravelCompanionApps\CompanionApplication;
use OpenSoutheners\LaravelCompanionApps\Platform;
use OpenSoutheners\LaravelCompanionApps\ServiceProvider;
use OpenSoutheners\LaravelCompanionApps\Support\Facades\Companion;

class CompanionApplicationTest extends TestCase
{
    public function test_companion_supports_links()
    {
        $this->assertFalse(
            CompanionApplication::make('com.example', Platform::Android)->supportsLinks()
        );

        $this->assertTrue(
            CompanionApplication::make('com.example', Platform::Android)
                ->linkScheme('test')
                ->supportsLinks()
        );
    }

    public function test_companion_app_link_redirects()
    {
        ServiceProvider::loadApplications([
            CompanionApplication::make('com.example', Platform::Android)->linkScheme('example')
        ]);

        Route::get('/', function () {
            return Companion::android('com.example')->link('foo/bar');
        });

        $this->get('/')->assertRedirect((string) Companion::android('com.example')->link('foo/bar'));
    }

    public function test_companion_app_link_redirects_mixin_works_same_way()
    {
        ServiceProvider::loadApplications([
            CompanionApplication::make('com.example', Platform::Android)->linkScheme('example')
        ]);

        Route::get('/', function () {
            return redirect()->toApp(Companion::android('com.example'), 'foo/bar');
        });

        $this->get('/')->assertRedirect((string) Companion::android('com.example')->link('foo/bar'));
    }

    public function test_companion_app_link_redirects_with_fallback()
    {
        ServiceProvider::loadApplications([
            CompanionApplication::make('com.example', Platform::Android)->linkScheme('example')
        ]);

        Route::get('/', function () {
            return Companion::android('com.example')
                ->link('foo/bar')
                ->fallbackUrl('https://google.com');
        });

        $this->get('/')->assertRedirect(
            (string) Companion::android('com.example')
                ->link('foo/bar')
                ->fallbackUrl('https://google.com')
        );
    }

    public function test_companion_app_get_store_platform()
    {
        $this->assertEquals('play', CompanionApplication::make('com.example', Platform::Android)->getPlatformStore());
        $this->assertEquals('itunes', CompanionApplication::make('com.example', Platform::Apple)->getPlatformStore());
    }

    public function test_companion_app_get_store_link()
    {
        $this->assertEquals(
            'https://play.google.com/store/apps/details?id=com.example',
            CompanionApplication::make('com.example', Platform::Android)->getStoreLink()
        );
    }

    public function test_companion_app_get_store_meta_tag()
    {
        $this->assertEquals(
            '<meta name="google-play-app" content="app-id=com.example">',
            CompanionApplication::make('com.example', Platform::Android)->getStoreLinkMetaTag()
        );
    }

    public function test_companion_app_get_store_badge_htmlaa()
    {
        $this->assertEquals(
            '<a target="_blank" href="https://play.google.com/store/apps/details?id=com.example"><img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" width="180" alt="" /></a>',
            CompanionApplication::make('com.example', Platform::Android)->getStoreBadgeHtml()
        );
    }

    public function test_companion_app_get_store_badge_html_with_different_region()
    {
        $this->assertEquals(
            '<a target="_blank" href="https://play.google.com/store/apps/details?id=com.example"><img src="https://play.google.com/intl/en_us/badges/static/images/badges/es_badge_web_generic.png" width="180" alt="" /></a>',
            CompanionApplication::make('com.example', Platform::Android)
                ->setStoreOptions(region: 'es')
                ->getStoreBadgeHtml()
        );
    }

    public function test_companion_app_get_store_badge_html_accepts_optional_args_as_badge_img_attributes()
    {
        $this->assertEquals(
            '<a target="_blank" href="https://play.google.com/store/apps/details?id=com.example"><img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" width="240" alt="download_my_app" /></a>',
            CompanionApplication::make('com.example', Platform::Android)
                ->setStoreOptions(region: 'en')
                ->getStoreBadgeHtml(width: 240, alt: 'download_my_app')
        );
    }

    public function test_companion_app_get_apple_app_store_badge_html_with_url_configured()
    {
        config(['companion.store.apple_badge_url' => 'https://foo.bar/app_store_badge.svg']);

        $this->assertEquals(
            '<a target="_blank" href="https://apps.apple.com/en/app/example_app/id123456789"><img src="https://foo.bar/app_store_badge.svg" width="180" alt="" /></a>',
            CompanionApplication::make('com.example', Platform::Apple)
                ->setStoreOptions('123456789', 'example_app')
                ->getStoreBadgeHtml()
        );
    }

    public function test_companion_app_get_apple_app_store_badge_html_with_url_configured_replaces_region()
    {
        config(['companion.store.apple_badge_url' => 'https://foo.bar/{region}/app_store_badge.svg']);

        $this->assertEquals(
            '<a target="_blank" href="https://apps.apple.com/en/app/example_app/id123456789"><img src="https://foo.bar/en/app_store_badge.svg" width="180" alt="" /></a>',
            CompanionApplication::make('com.example', Platform::Apple)
                ->setStoreOptions('123456789', 'example_app')
                ->getStoreBadgeHtml()
        );
    }
}
