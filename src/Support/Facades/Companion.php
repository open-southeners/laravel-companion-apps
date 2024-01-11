<?php

namespace OpenSoutheners\LaravelCompanionApps\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
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
