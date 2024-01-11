<?php

namespace OpenSoutheners\LaravelCompanionApps;
use OpenSoutheners\LaravelCompanionApps\Exceptions\CompanionAppNotFound;

class Manager
{
    /**
     * @var array<string, array<\OpenSoutheners\LaravelCompanionApps\Companion>>
     */
    protected static array $registeredApps = [];

    /**
     * Register companion application.
     */
    public static function register(Companion $app): void
    {
        static::$registeredApps[$app->getPlatform()][$app->getName()] = $app;
    }

    /**
     * Find application from registered by platform and/or name.
     * 
     * @throws \OpenSoutheners\LaravelCompanionApps\Exceptions\CompanionAppNotFound
     * @return array<\OpenSoutheners\LaravelCompanionApps\Companion>|\OpenSoutheners\LaravelCompanionApps\Companion
     */
    public static function applicationsByPlatform(Platform $platform, ?string $name = null): array|Companion
    {
        $platformApps = self::$registeredApps[$platform->name] ?? [];

        if (! $name) {
            return $platformApps;
        }

        $foundApp = $platformApps[$name] ?? null;

        if (! $foundApp) {
            throw CompanionAppNotFound::forPlatform($name, $platform);
        }

        return $foundApp;
    }

    /**
     * Get all registered companion applications.
     *
     * @return array<\OpenSoutheners\LaravelCompanionApps\Companion>
     */
    public static function apps(): array
    {
        $appsArray = [];

        foreach (static::$registeredApps as $apps) {
            $appsArray = array_merge($appsArray, array_values($apps));
        }

        return $appsArray;
    }

    /**
     * Get HTML meta tags with links to your registered apps for your web pages.
     */
    public static function companionAppsHeader(): string
    {
        $metaTags = [];

        foreach (self::apps() as $app) {
            $metaTags[] = $app->getStoreLinkMetaTag();
        }

        return implode("\n", array_filter($metaTags));
    }
}
