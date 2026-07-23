<?php

namespace App\Console\Commands;

use App\Models\DemoBatch;
use App\Support\Demo\DemoBatchTracker;
use App\Support\Demo\DemoGate;
use Illuminate\Console\Command;
use RuntimeException;

class DemoClearCommand extends Command
{
    protected $signature = 'demo:clear {--force : Skip confirmation}';

    protected $description = 'Remove only tagged demo-batch data. Leaves catalog, roles, settings, and real admins.';

    public function handle(DemoBatchTracker $tracker): int
    {
        try {
            DemoGate::assertCanClear();
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $active = DemoBatch::query()->active()->count();
        if ($active < 1) {
            $this->warn('No active demo batches found.');

            return self::SUCCESS;
        }

        $this->warn("This will delete {$active} active demo batch(es) and all tagged demo rows.");
        $this->line('Database: '.(string) config('database.connections.'.config('database.default').'.database'));
        $this->line('Real admins, roles, permissions, catalog, and system settings are kept.');

        if (! $this->option('force')) {
            if (! $this->confirm('Delete demo data only?', false)) {
                $this->warn('Cancelled.');

                return self::SUCCESS;
            }
            if (strtoupper((string) $this->ask('Type YES to confirm')) !== 'YES') {
                $this->warn('Cancelled.');

                return self::SUCCESS;
            }
        }

        $result = $tracker->clearAllActiveBatches();
        $this->info("Cleared {$result['batches']} batch(es), removed ~{$result['records']} tracked rows (+ demo activity/KPIs).");
        $this->line('Refresh overview: php artisan analytics:rollup-kpis');

        return self::SUCCESS;
    }
}
