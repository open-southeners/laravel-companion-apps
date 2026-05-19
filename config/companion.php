<?php

return [

    'files' => [

        /**
         * Disk used to store command generated files.
         *
         * Defaults to local file when empty.
         */
        'disk' => '',

        /**
         * Path relative to base path of the disk where to store generated files.
         *
         * This must be preferably to the public folder that represents your root, e.g.:
         *
         * public/.well-known/assetlinks.json => https://my_website.com/.well-known/assetlinks.json
         * public/manifest.json => https://my_website.com/manifest.json
         */
        'base_path' => 'public',

    ],

    'store' => [

        /**
         * Two-letter ISO locale for the store.
         *
         * Can be override on each app by using setStoreOptions method.
         *
         * Default uses the Laravel app locale.
         */
        'region' => null,

        /**
         * Apple's App Store badge URL where either you host it or use the default (lack of region support unlike Android).
         *
         * You can officially download them from here:
         * https://developer.apple.com/app-store/marketing/guidelines/
         *
         * Add {region} in case you want to manage multiple badges across multiple regions (locales).
         *
         * Static URL (from public/ folder): 'app_store/{region}/black_badge.svg'
         * Or just a string as external img: https://cdn.my_host.com/app_store/{region}/black_badge.svg
         */
        'apple_badge_url' => 'https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg',

        /**
         * Android's Play Store badge URL where either you host it or use the default.
         *
         * You can officially download them from here:
         * https://play.google.com/intl/en_us/badges/
         *
         * Add {region} in case you want to manage multiple badges across multiple regions (locales).
         *
         * Static URL (from public/ folder): 'play_store/{region}/black_badge.svg'
         * Or just a string as external img: https://cdn.my_host.com/play_store/{region}/black_badge.svg
         */
         'android_badge_url' => 'https://play.google.com/intl/en_us/badges/static/images/badges/{region}_badge_web_generic.png',

    ],

    'pwa' => [

        /**
         * Enable Progressive Web App helpers and manifest generation.
         */
        'enabled' => true,

        /**
         * Web app manifest path exposed from the public root.
         */
        'manifest_path' => '/manifest.json',

        /**
         * Core web app manifest fields.
         */
        'name' => env('APP_NAME', 'Laravel'),
        'short_name' => env('APP_NAME', 'Laravel'),
        'description' => null,
        'start_url' => '/',
        'scope' => '/',
        'display' => 'standalone',
        'orientation' => null,
        'theme_color' => '#111827',
        'background_color' => '#ffffff',

        /**
         * Icons used by browsers when installing the web app.
         */
        'icons' => [
            [
                'src' => '/icons/icon-192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
            ],
            [
                'src' => '/icons/icon-512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
            ],
            [
                'src' => '/icons/maskable-512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ],

        /**
         * Optional manifest enhancements.
         */
        'shortcuts' => [],
        'screenshots' => [],
        'share_target' => null,
        'protocol_handlers' => [],
        'related_applications' => true,
        'prefer_related_applications' => false,

        /**
         * Structured manifest and tag config used by the package internals.
         *
         * Top-level keys above remain available for backward compatibility.
         */
        'manifest' => [
            'name' => null,
            'short_name' => null,
            'description' => null,
            'id' => null,
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => null,
            'theme_color' => '#111827',
            'background_color' => '#ffffff',
            'lang' => null,
            'dir' => null,
            'categories' => [],
            'icons' => [],
            'screenshots' => [],
            'shortcuts' => [],
            'share_target' => null,
            'protocol_handlers' => [],
            'related_applications' => true,
            'prefer_related_applications' => false,
        ],

        'tags' => [
            'theme_color' => null,
            'mobile_web_app_capable' => true,
            'apple_mobile_web_app_capable' => true,
            'apple_mobile_web_app_title' => null,
            'apple_mobile_web_app_status_bar_style' => 'default',
            'apple_touch_icons' => [],
        ],

        /**
         * Service worker generation settings.
         */
        'service_worker' => [
            'enabled' => false,
            'path' => '/service-worker.js',
            'cache_name' => 'companion-app-v1',
            'offline_url' => '/offline',
            'precache' => [
                '/',
                '/offline',
                '/manifest.json',
            ],
            'asset_patterns' => [
                '/build/',
                '/css/',
                '/js/',
                '/images/',
                '/icons/',
            ],
        ],

        /**
         * Default Blade view applications can use for an offline fallback route.
         */
        'offline_view' => 'companion::offline',

        /**
         * Apple-specific install metadata.
         */
        'apple_touch_icon' => '/icons/apple-touch-icon.png',
        'apple_mobile_web_app_capable' => true,

    ],

];
