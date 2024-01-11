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
}
