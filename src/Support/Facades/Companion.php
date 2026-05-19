<?php

namespace OpenSoutheners\LaravelCompanionApps\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<\OpenSoutheners\LaravelCompanionApps\CompanionApplication> list()
 * @method static array<\OpenSoutheners\LaravelCompanionApps\CompanionApplication> listByPlatform(\OpenSoutheners\LaravelCompanionApps\Platform $platform)
 * @method static \OpenSoutheners\LaravelCompanionApps\CompanionApplication getByName(\OpenSoutheners\LaravelCompanionApps\Platform $platform, string $name)
 * @method static \OpenSoutheners\LaravelCompanionApps\CompanionApplication android(string $name)
 * @method static \OpenSoutheners\LaravelCompanionApps\CompanionApplication apple(string $name)
 * @method static \OpenSoutheners\LaravelCompanionApps\CompanionApplication web(string $name)
 * @method static string metaTags()
 * @method static string pwaTags()
 *
 * @see \OpenSoutheners\LaravelCompanionApps\Support\Companion
 */
class Companion extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'companion';
    }
}
