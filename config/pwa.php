<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Would you like the install button to appear on all pages?
      Set true/false
    |--------------------------------------------------------------------------
    */

    'install-button' => false,

    /*
    |--------------------------------------------------------------------------
    | PWA Manifest Configuration
    |--------------------------------------------------------------------------
    |  php artisan erag:update-manifest
    */

    'manifest' => [
        'name' => '7th Trade Hub',
        'short_name' => '7thHub',
        'background_color' => '#0F172A',
        'display' => 'standalone',
        'description' => '7th Trade Hub — digital services marketplace (crypto exchange, social growth, templates, listings).',
        'theme_color' => '#0B6A39',
        'icons' => [
            [
                'src' => '/icons/icon-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Configuration
    |--------------------------------------------------------------------------
    | Toggles the application's debug mode based on the environment variable
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Livewire Integration
    |--------------------------------------------------------------------------
    | Set to true if you're using Livewire in your application to enable
    | Livewire-specific PWA optimizations or features.
    */

    'livewire-app' => false,

    /*
    |--------------------------------------------------------------------------
    | Static icon fallbacks (when admin media is not on disk yet)
    |--------------------------------------------------------------------------
    | Used by branding:sync-pwa before the green placeholder is written.
    */
    'default_icon_paths' => [
        'assets/images/favicon.png',
        'assets/images/favicon.ico',
        'assets/images/white_originla_logo.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Android APK download (home page + optional direct link)
    |--------------------------------------------------------------------------
    */
    'apk' => [
        'enabled' => env('APK_DOWNLOAD_ENABLED', true),
        'download_url' => env('APK_DOWNLOAD_URL', '/downloads/7th-trade-hub.apk'),
        'version' => env('APK_VERSION', ''),
    ],
];
