<?php

namespace App\Console\Commands;

use App\Support\Demo\DemoGate;
use Illuminate\Console\Command;
use RuntimeException;

class DemoFreshCommand extends Command
{
    protected $signature = 'demo:fresh {--force : Skip confirmation}';

    protected $description = 'WIPE database (migrate:fresh) + full seed including demo. Prefer demo:seed on pre-launch production.';

    public function handle(): int
    {
        try {
            DemoGate::assertCanDestructive();
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->error('WARNING: This WIPE the entire database (migrate:fresh), then reseed.');
        $this->line('Database: '.(string) config('database.connections.'.config('database.default').'.database'));
        $this->line('APP_ENV: '.app()->environment());

        if (! $this->option('force')) {
            if (! $this->confirm('Wipe and rebuild from scratch?', false)) {
                $this->warn('Cancelled.');

                return self::SUCCESS;
            }
            if (strtoupper((string) $this->ask('Type YES to confirm destructive wipe')) !== 'YES') {
                $this->warn('Cancelled.');

                return self::SUCCESS;
            }
        }

        // Ensure DatabaseSeeder demo gate is open
        putenv('ALLOW_DEMO_DATA=true');
        $_ENV['ALLOW_DEMO_DATA'] = 'true';
        $_SERVER['ALLOW_DEMO_DATA'] = 'true';
        putenv('SEED_DEMO_DATA=true');
        $_ENV['SEED_DEMO_DATA'] = 'true';
        $_SERVER['SEED_DEMO_DATA'] = 'true';
        config(['demo.allow_demo_data' => true]);

        $this->info('Running migrate:fresh --seed ...');
        $this->call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        $this->newLine();
        $this->info('Demo fresh complete.');
        $this->line('  Launch cleanup later: php artisan demo:clear --force');

        return self::SUCCESS;
    }
}
