<?php

namespace App\Support\Demo;

use RuntimeException;

class DemoGate
{
    public static function allowDemoData(): bool
    {
        return (bool) config('demo.allow_demo_data', false);
    }

    public static function allowDestructive(): bool
    {
        if (self::allowDemoData() && ! app()->environment('production')) {
            return true;
        }

        return (bool) config('demo.allow_destructive_seeders', false);
    }

    /**
     * Non-destructive demo:seed / DemoPlatformSeeder.
     * Allowed whenever ALLOW_DEMO_DATA=true, including APP_ENV=production.
     */
    public static function assertCanSeed(): void
    {
        if (! self::allowDemoData()) {
            throw new RuntimeException(
                'Demo seeding refused: set ALLOW_DEMO_DATA=true (or SEED_DEMO_DATA=true) in .env, then php artisan config:clear.'
            );
        }
    }

    /**
     * demo:fresh (migrate:fresh) — more dangerous.
     */
    public static function assertCanDestructive(): void
    {
        self::assertCanSeed();

        if (! self::allowDestructive()) {
            throw new RuntimeException(
                'Destructive demo:fresh refused: set ALLOW_DESTRUCTIVE_SEEDERS=true for production wipe, or use APP_ENV=staging/local with ALLOW_DEMO_DATA=true. Prefer demo:seed + demo:clear on pre-launch production.'
            );
        }
    }

    public static function assertCanClear(): void
    {
        // Clearing is allowed when demo was permitted OR leftover demo batches exist;
        // still require the flag so live sites don't clear by accident without intent.
        if (! self::allowDemoData() && ! self::allowDestructive()) {
            throw new RuntimeException(
                'Demo clear refused: set ALLOW_DEMO_DATA=true to remove demo rows (or ALLOW_DESTRUCTIVE_SEEDERS=true).'
            );
        }
    }
}
