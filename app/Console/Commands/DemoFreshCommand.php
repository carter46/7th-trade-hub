<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoFreshCommand extends Command
{
    protected $signature = 'demo:fresh {--force : Skip confirmation}';

    protected $description = 'migrate:fresh + core seeders + DemoPlatformSeeder with checklist (blocked in production)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refused: demo:fresh cannot run when APP_ENV=production.');

            return self::FAILURE;
        }

        if (! app()->environment('local', 'testing') && ! filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->error('Refused: set SEED_DEMO_DATA=true for non-local environments.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This will WIPE the database (migrate:fresh) and reseed demo data. Continue?', false)) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }

        // Ensure demo gate is open for DatabaseSeeder
        putenv('SEED_DEMO_DATA=true');
        $_ENV['SEED_DEMO_DATA'] = 'true';
        $_SERVER['SEED_DEMO_DATA'] = 'true';

        $this->info('Running migrate:fresh --seed ...');
        $this->call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        $this->newLine();
        $this->info('Demo fresh complete.');
        $this->line('  ✓ Users / admins');
        $this->line('  ✓ Wallets / transactions');
        $this->line('  ✓ Listings / orders / escrows');
        $this->line('  ✓ Tickets / KYC');
        $this->line('  ✓ Audit / notifications / analytics');
        $this->info('Platform ready for demo.');

        return self::SUCCESS;
    }
}
