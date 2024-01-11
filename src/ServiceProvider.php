<?php

namespace OpenSoutheners\LaravelCompanionApps;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use OpenSoutheners\LaravelCompanionApps\Commands;
use OpenSoutheners\LaravelCompanionApps\Support\Companion;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Registered companion applications within Laravel.
     * 
     * @var array<string, array<string, \OpenSoutheners\LaravelCompanionApps\CompanionApplication>>
     */
    public static array $registeredCompanionApps = [];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Routing\Redirector::mixin(new Redirector);

        $this->app->bind('companion', fn () => new Companion);

        Blade::directive('companionMetaTags', function (Application $app): string {
            return $app->make('companion')->metaTags();
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands([
            Commands\GenerateCommand::class,
        ]);
    }

    /**
     * Load companion applications to the registered array.
     * 
     * @param array<\OpenSoutheners\LaravelCompanionApps\CompanionApplication> $applications
     */
    public static function loadApplications(array $applications): void
    {
        foreach ($applications as $application) {
            self::$registeredCompanionApps[$application->getPlatform()][$application->getName()] = $application;
        }
    }
}
