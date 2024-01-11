<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use OpenSoutheners\LaravelCompanionApps\AppLink;
use OpenSoutheners\LaravelCompanionApps\CompanionApplication;
use OpenSoutheners\LaravelCompanionApps\Platform;

class AppLinkTest extends TestCase
{
    public function test_app_link_intent_fallback_url_gets_appended()
    {
        $app = CompanionApplication::make('com.example', Platform::Android);

        $appLink = new AppLink($app, 'example');

        $this->assertStringContainsString(
            'S.browser_fallback_url=https://google.com',
            (string) $appLink->getIntent('foo/bar')->fallbackUrl('https://google.com')
        );
    }
}
