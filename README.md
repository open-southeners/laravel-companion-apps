Laravel Companion Apps [![required php version](https://img.shields.io/packagist/php-v/open-southeners/laravel-companion-apps)](https://www.php.net/supported-versions.php) [![codecov](https://codecov.io/gh/open-southeners/laravel-companion-apps/branch/main/graph/badge.svg?token=8aZCqhDfb3)](https://codecov.io/gh/open-southeners/laravel-companion-apps) [![Edit on VSCode online](https://img.shields.io/badge/vscode-edit%20online-blue?logo=visualstudiocode)](https://vscode.dev/github/open-southeners/laravel-companion-apps)
===

Extend your Laravel applications with companions apps (Android, Apple, Progressive Web Applications...).

## Key features

- Allow app links (also called [deep links](https://developer.android.com/training/app-links)) on Android apps:
    - Generates the `.well-known/assetlinks.json` file for app links verification
    - Redirects to app links (`redirect()->toApp(Companion::android('com.my_company.my_app'), 'products/1')` or `Companion::android('com.my_company.my_app')->redirect('products/1')`)
    - Generates app links (`Companion::android('com.my_company.my_app')->link('products/1')`)
- Allow app links (also called [universal links](https://developer.apple.com/documentation/xcode/supporting-universal-links-in-your-app)) on iOS apps:
    - Generates the `apple-app-site-association` file for app links verification
    - Redirects to app links (`redirect()->toApp(Companion::ios('com.my_company.my_app'))` or `Companion::ios('com.my_company.my_app')->redirect('products/1')`)
    - Generates app links (`Companion::ios('com.my_company.my_app')->link('products/1')->fallbackToStore()`)
- Add smart banner (**Apple only for now**) using Blade directive `@companionMetaTags` or facade's method `app('companion')->metaTags()`

## Getting started

```bash
composer require open-southeners/laravel-companion-apps
```

## Documentation

To learn how to use everything you should check the [official documentation](https://docs.opensoutheners.com/laravel-companion-apps).

## Partners

[![skore logo](https://github.com/open-southeners/partners/raw/main/logos/skore_logo.png)](https://getskore.com)

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
