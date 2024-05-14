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

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('companion', include __DIR__.'/../config/companion.php');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Empty all registered applications from tests before
        ServiceProvider::$registeredCompanionApps = [];
    }
}
