<?php

namespace OpenSoutheners\LaravelCompanionApps\Support;

use OpenSoutheners\LaravelCompanionApps\CompanionApplication;
use OpenSoutheners\LaravelCompanionApps\Exceptions\CompanionAppNotFound;
use OpenSoutheners\LaravelCompanionApps\Platform;
use OpenSoutheners\LaravelCompanionApps\ServiceProvider;

class Companion
{
    /**
     * Get all registered applications.
     *
     * @return array<\OpenSoutheners\LaravelCompanionApps\CompanionApplication>
     */
    public static function list(): array
    {
        $appsArray = [];

        foreach (ServiceProvider::$registeredCompanionApps as $apps) {
            $appsArray = array_merge($appsArray, array_values($apps));
        }

        return $appsArray;
    }

    /**
     * List applications by platform.
     * 
     * @throws \OpenSoutheners\LaravelCompanionApps\Exceptions\CompanionAppNotFound
     * @return array<\OpenSoutheners\LaravelCompanionApps\CompanionApplication>
     */
    public static function listByPlatform(Platform $platform): array
    {
        return ServiceProvider::$registeredCompanionApps[$platform->name] ?? [];
    }

    /**
     * Get application by platform and name.
     * 
     * @throws \OpenSoutheners\LaravelCompanionApps\Exceptions\CompanionAppNotFound
     */
    public static function getByName(Platform $platform, string $name): CompanionApplication
    {
        $foundApp = self::listByPlatform($platform)[$name] ?? null;

        if (! $foundApp) {
            throw CompanionAppNotFound::forPlatform($name, $platform);
        }

        return $foundApp;
    }

    /**
     * Get Android application by name.
     * 
     * @throws \OpenSoutheners\LaravelCompanionApps\Exceptions\CompanionAppNotFound
     */
    public static function android(string $name): CompanionApplication
    {
        return self::getByName(Platform::Android, $name);
    }

    /**
     * Get Apple application by name.
     * 
     * @throws \OpenSoutheners\LaravelCompanionApps\Exceptions\CompanionAppNotFound
     */
    public static function apple(string $name): CompanionApplication
    {
        return self::getByName(Platform::Apple, $name);
    }

    /**
     * Get web application by name.
     * 
     * @throws \OpenSoutheners\LaravelCompanionApps\Exceptions\CompanionAppNotFound
     */
    public static function web(string $name): CompanionApplication
    {
        return self::getByName(Platform::Web, $name);
    }

    /**
     * Get HTML meta tags with links to your registered apps for your web pages.
     */
    public static function metaTags(): string
    {
        $metaTags = [];

        foreach (self::list() as $app) {
            $metaTags[] = $app->getStoreLinkMetaTag();
        }

        return implode("\n", array_filter($metaTags));
    }

    /**
     * Get HTML tags required to expose Progressive Web App metadata.
     */
    public static function pwaTags(): string
    {
        /** @var array<string, mixed> $pwa */
        $pwa = config('companion.pwa', []);

        if (($pwa['enabled'] ?? true) === false) {
            return '';
        }

        /** @var array<string, mixed> $tagConfig */
        $tagConfig = $pwa['tags'] ?? [];
        /** @var array<string, mixed> $manifestConfig */
        $manifestConfig = $pwa['manifest'] ?? [];

        $appName = e(self::stringValue(
            $tagConfig['apple_mobile_web_app_title']
            ?? $manifestConfig['short_name']
            ?? $pwa['short_name']
            ?? $manifestConfig['name']
            ?? $pwa['name']
            ?? config('app.name', 'Laravel'),
            'Laravel'
        ));
        $manifestPath = self::stringValue($pwa['manifest_path'] ?? null, '/manifest.json');
        $manifestUrl = e(str_starts_with($manifestPath, 'http') ? $manifestPath : asset($manifestPath));
        $themeColor = $tagConfig['theme_color'] ?? $pwa['theme_color'] ?? $manifestConfig['theme_color'] ?? null;
        $statusBarStyle = self::stringValue($tagConfig['apple_mobile_web_app_status_bar_style'] ?? null, 'default');

        $tags = [
            "<link rel=\"manifest\" href=\"{$manifestUrl}\">",
        ];

        if ($themeColor) {
            $tags[] = '<meta name="theme-color" content="'.e(self::stringValue($themeColor)).'">';
        }

        if (($tagConfig['mobile_web_app_capable'] ?? true) === true) {
            $tags[] = '<meta name="mobile-web-app-capable" content="yes">';
        }

        foreach (self::appleTouchIcons($tagConfig, $pwa) as $icon) {
            $tags[] = '<link '.self::htmlAttributes($icon).'>';
        }

        if (($tagConfig['apple_mobile_web_app_capable'] ?? ($pwa['apple_mobile_web_app_capable'] ?? true)) === true) {
            $tags[] = '<meta name="apple-mobile-web-app-capable" content="yes">';
            $tags[] = "<meta name=\"apple-mobile-web-app-title\" content=\"{$appName}\">";
            $tags[] = '<meta name="apple-mobile-web-app-status-bar-style" content="'.e($statusBarStyle).'">';
        }

        return implode("\n", $tags);
    }

    /**
     * @param  array<string, mixed>  $tagConfig
     * @param  array<string, mixed>  $pwa
     * @return array<int, array<string, string>>
     */
    protected static function appleTouchIcons(array $tagConfig, array $pwa): array
    {
        $configuredIcons = $tagConfig['apple_touch_icons'] ?? [];

        if (is_array($configuredIcons) && $configuredIcons !== []) {
            return array_values(array_filter(array_map(function ($icon): ?array {
                if (is_string($icon)) {
                    return [
                        'rel' => 'apple-touch-icon',
                        'href' => e(str_starts_with($icon, 'http') ? $icon : asset($icon)),
                    ];
                }

                if (! is_array($icon)) {
                    return null;
                }

                $href = $icon['href'] ?? $icon['src'] ?? null;

                if (! is_string($href) || $href === '') {
                    return null;
                }

                return array_filter([
                    'rel' => e(self::stringValue($icon['rel'] ?? null, 'apple-touch-icon')),
                    'href' => e(str_starts_with($href, 'http') ? $href : asset($href)),
                    'sizes' => isset($icon['sizes']) ? e(self::stringValue($icon['sizes'])) : null,
                    'type' => isset($icon['type']) ? e(self::stringValue($icon['type'])) : null,
                    'media' => isset($icon['media']) ? e(self::stringValue($icon['media'])) : null,
                ], static fn ($value): bool => $value !== null && $value !== '');
            }, $configuredIcons)));
        }

        $legacyIcon = $pwa['apple_touch_icon'] ?? null;

        if (! is_string($legacyIcon) || $legacyIcon === '') {
            return [];
        }

        return [[
            'rel' => 'apple-touch-icon',
            'href' => e(str_starts_with($legacyIcon, 'http') ? $legacyIcon : asset($legacyIcon)),
        ]];
    }

    /**
     * @param  array<string, string>  $attributes
     */
    protected static function htmlAttributes(array $attributes): string
    {
        $html = [];

        foreach ($attributes as $name => $value) {
            $html[] = $name.'="'.$value.'"';
        }

        return implode(' ', $html);
    }

    protected static function stringValue(mixed $value, string $default = ''): string
    {
        return is_scalar($value) ? (string) $value : $default;
    }
}
