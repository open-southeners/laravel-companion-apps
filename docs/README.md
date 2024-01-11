---
description: Installing package into your Laravel application.
---

# Getting started

Grab the dependency with Composer:

```bash
composer require open-southeners/laravel-companion-apps
```

### Publish config file

```bash
php artisan vendor:publish --provider="OpenSoutheners\\LaravelCompanionApps\\ServiceProvider"
```

### Register your applications

To register your companion applications you should call this on your `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use OpenSoutheners\LaravelCompanionApps\CompanionApplication;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rest of your code...
        
        \OpenSoutheners\LaravelCompanionApps\ServiceProvider::loadApplications([
            CompanionApplication::make('com.example', Platform::Android)
                ->linkScheme('example'),

            CompanionApplication::make('com.example_preview', Platform::Android)
                ->linkScheme('example'),

            CompanionApplication::make('com.example', Platform::Apple)
                ->linkScheme('example')
                ->setStoreOptions(id: '123456789', slug: 'example_app')
        ]);
    }
}
```
