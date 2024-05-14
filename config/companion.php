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

];
