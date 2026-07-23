<?php

namespace App\Console\Commands;

use App\Services\Analytics\Providers\GoogleAnalyticsProvider;
use Illuminate\Console\Command;

class AnalyticsSyncGa extends Command
{
    protected $signature = 'analytics:sync-ga';

    protected $description = 'Sync Google Analytics snapshots';

    public function handle(GoogleAnalyticsProvider $provider): int
    {
        if (! $provider->isEnabled()) {
            $this->warn('Google Analytics is disabled.');

            return self::SUCCESS;
        }

        $count = $provider->syncSnapshots();
        $this->info("Synced {$count} GA snapshot(s).");

        return self::SUCCESS;
    }
}
