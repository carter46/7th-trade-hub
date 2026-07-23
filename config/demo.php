<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Allow demo data seeding
    |--------------------------------------------------------------------------
    |
    | Separate from APP_ENV. Pre-launch production may keep APP_ENV=production
    | while ALLOW_DEMO_DATA=true so realistic demo rows can be inserted.
    | On real launch day set this to false and run: php artisan demo:clear
    |
    | SEED_DEMO_DATA is an alias for backwards compatibility.
    */
    'allow_demo_data' => (bool) filter_var(
        env(
            'ALLOW_DEMO_DATA',
            env('SEED_DEMO_DATA', env('APP_ENV') === 'local' ? 'true' : 'false')
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Allow destructive demo commands (migrate:fresh)
    |--------------------------------------------------------------------------
    |
    | Required for demo:fresh when APP_ENV=production. Local/staging can use
    | demo:fresh when allow_demo_data is true without this flag.
    */
    'allow_destructive_seeders' => (bool) filter_var(
        env('ALLOW_DESTRUCTIVE_SEEDERS', false),
        FILTER_VALIDATE_BOOLEAN
    ),
];
