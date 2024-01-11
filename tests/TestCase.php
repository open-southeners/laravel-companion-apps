<?php

namespace OpenSoutheners\LaravelCompanionApps\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use OpenSoutheners\LaravelCompanionApps\ServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }
}
