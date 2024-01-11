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
         * Can be override on each app by using setStoreOptions method
         */
        'region' => 'en',

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
        'apple_badge_url' => '',

    ],

];
