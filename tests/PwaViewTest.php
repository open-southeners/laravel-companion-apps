<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use Illuminate\Support\Facades\Blade;

class PwaViewTest extends TestCase
{
    public function test_offline_view_is_available()
    {
        $this->assertSame('companion::offline', config('companion.pwa.offline_view'));

        $html = view(config('companion.pwa.offline_view'))->render();

        $this->assertStringContainsString('You are offline', $html);
        $this->assertStringContainsString('Please check your connection and try again.', $html);
    }

    public function test_install_button_component_renders_progressive_enhancement_markup()
    {
        $html = Blade::render(<<<'BLADE'
<x-companion-install-button ios-message="Install it from the share menu.">
    Add to home screen
</x-companion-install-button>
BLADE);

        $this->assertStringContainsString('data-companion-install', $html);
        $this->assertStringContainsString('Add to home screen', $html);
        $this->assertStringContainsString('beforeinstallprompt', $html);
        $this->assertStringContainsString('data-companion-install-ios', $html);
        $this->assertStringContainsString('Install it from the share menu.', $html);
    }
}
