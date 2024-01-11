---
description: Usage of companion applications within your application
---

# Usage

This package allows you to add the following features to your Laravel application:

* Smart banner (Apple only for now),&#x20;
* Universal links (Apple only) which uses the current page path (URL) to continue navigation through the application when smart banner is used (**meta tag must be injected to the HTML header**)
* App links which lets you **redirect users from your Laravel app to any native application** installed on the user device or, optionally, **to the store if none was found**.
* Show app stores banners with links to your registered applications.
* Generate app links verification files and [web app manifest](https://developer.mozilla.org/en-US/docs/Web/Manifest).

## Generate files

{% hint style="info" %}
This is required to be able to use App links on your mobile applications but there is some extra steps that you should make on them.

[Deep links documentation page for Android](https://developer.android.com/training/app-links/deep-linking).

[Universal Links documentation page for Apple](https://developer.apple.com/documentation/xcode/supporting-universal-links-in-your-app).
{% endhint %}

If you need to verify app links on your native mobile applications (iOS, Android) this package allows you to do that in a interactive way with this artisan command:

```bash
php artisan app:companion:generate
```

This command will ask some questions like list of SHA2 certificate fingerprints (if you registered any Android app) and site paths associated to the Apple applications (if you registered any).

And at the end will ask for generate a manifest.json for your Laravel web app which will recommend the user to any of the applications registered whenever the devices matches a criteria. [Learn more here](https://developer.mozilla.org/en-US/docs/Web/Manifest/related\_applications).

At the end you should have the following files on your public folder (this also configurable through `config/companion.php`):

<figure><img src=".gitbook/assets/image.png" alt="" width="375"><figcaption></figcaption></figure>

## Adding smart banner

<figure><img src="https://docs-assets.developer.apple.com/published/20ab7d726b/rendered2x-1605721898.png" alt="" width="188"><figcaption><p>An iOS device showing the smart banner from Safari navigating into a website</p></figcaption></figure>

To show to Apple devices users a banner like the one shown whenever they're navigating in your Laravel application you have the following Blade directive:

```php
@companionMetaTags
```

You also have the method if you're not using Blade:

```php
use OpenSoutheners\LaravelCompanionApps\Support\Facades\Companion;

app('companion')->metaTags();

// or

Companion::metaTags();
```

{% hint style="warning" %}
Is recommended that you add this to the `<head>` part of your application otherwise it might now work.
{% endhint %}

## Getting store banner HTML

You also can show application banner images like the following example using Blade:

```php
{!! app('companion')->android('com.example')->getStoreBadgeHtml(); !!}
{!! app('companion')->apple('com.example')->getStoreBadgeHtml(); !!}
```

If you want to customise its size or alt on the rendered img HTML tag you can pass the following to the arguments:

```php
{!! app('companion')->android('com.example')->getStoreBadgeHtml(width: 240, alt: 'download_my_app'); !!}
```

### Localise the banner image

**By default this package only translates automatically the Android badge,** for the Apple apps you might need to replace the `companion.php` option `store.apple_badge_url`:

```php
/**
 * Apple App Store badge url where you host it as Android host its own badges.
 * 
 * You can officially download them from here:
 * https://developer.apple.com/app-store/marketing/guidelines/
 * 
 * Add {region} in case you want to manage multiple badges across multiple regions (locales).
 * 
 * Static badge url: asset('app_store_badge.svg')
 * Using Laravel helper functions: asset('app_store/{region}/black_badge.svg')
 * Or just a string as external img: https://cdn.my_host.com/app_store/{region}/black_badge.svg
 */
 'apple_badge_url' => asset('app_store/{region}/black_badge.svg'),
```

The example above will output the following image whenever a user from Spain visits your website where you show the App Store badges:

```html
<img src="https://your_laravel_app.com/app_store/es/black_badge.svg" ...>
```
