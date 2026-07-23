<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\Demo\DemoPlatformSeeder;
use Database\Seeders\MarketplaceListingSeeder;
use Illuminate\Console\Command;

class DemoSeedCommand extends Command
{
    protected $signature = 'demo:seed {--force : Skip confirmation}';

    protected $description = 'Seed realistic demo platform data (never runs when APP_ENV=production)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refused: demo:seed cannot run when APP_ENV=production.');

            return self::FAILURE;
        }

        if (! app()->environment('local', 'testing') && ! filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->error('Refused: set SEED_DEMO_DATA=true for non-local environments.');

            return self::FAILURE;
        }

        if (User::query()->where('email', 'alice@example.com')->exists()) {
            $this->error('Demo personas already exist. Use `php artisan demo:fresh --force` to wipe and rebuild.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Seed demo platform data into the current database?', true)) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }

        // Listings volume target (~100) needs vendor samples + DemoMarketplaceSeeder.
        $this->call('db:seed', [
            '--class' => MarketplaceListingSeeder::class,
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => DemoPlatformSeeder::class,
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
