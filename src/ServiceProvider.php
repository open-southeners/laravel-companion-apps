<?php

namespace OpenSoutheners\LaravelCompanionApps;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use OpenSoutheners\LaravelCompanionApps\Commands;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Routing\Redirector::mixin(new Redirector);

        Blade::directive('companionAppsHead', function (): string {
            return Manager::companionAppsHeader();
        });

        $this->commands([
            Commands\GenerateCommand::class,
        ]);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 
    }
}
