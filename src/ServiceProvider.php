<?php

namespace OpenSoutheners\LaravelCompanionApps;

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

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/companion.php' => config_path('companion.php'),
            ], 'config');
        }

        Blade::directive('companionMetaTags', fn (): string => "<?php echo app('companion')->metaTags(); ?>");

        Blade::directive('companionPwaTags', fn (): string => "<?php echo app('companion')->pwaTags(); ?>");

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'companion');

        Blade::component('companion::components.install-button', 'companion-install-button');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/companion'),
            ], 'companion-views');
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('companion', fn () => new Companion);

        $this->commands([
            Commands\GenerateCommand::class,
            Commands\DoctorCommand::class,
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
