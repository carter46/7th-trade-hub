<?php

namespace App\Console\Commands;

use App\Models\MonitoringHeartbeat as MonitoringHeartbeatModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MonitoringHeartbeat extends Command
{
    protected $signature = 'monitoring:heartbeat';

    protected $description = 'Record monitoring heartbeat with system health payload';

    public function handle(): int
    {
        $diskTotal = @disk_total_space(base_path()) ?: 0;
        $diskFree = @disk_free_space(base_path()) ?: 0;

        $cacheOk = null;
        try {
            Cache::put('monitoring.ping', 'ok', 60);
            $cacheOk = Cache::get('monitoring.ping') === 'ok';
        } catch (\Throwable) {
            $cacheOk = false;
        }

        $failedJobs = null;
        try {
            if (Schema::hasTable('failed_jobs')) {
                $failedJobs = (int) DB::table('failed_jobs')->count();
            }
        } catch (\Throwable) {
            $failedJobs = null;
        }

        $payload = [
            'disk_total_bytes' => $diskTotal,
            'disk_free_bytes' => $diskFree,
            'cache_ok' => $cacheOk,
            'failed_jobs' => $failedJobs,
            'php_version' => PHP_VERSION,
        ];

        MonitoringHeartbeatModel::query()->updateOrCreate(
            ['key' => 'monitoring'],
            ['payload' => $payload, 'recorded_at' => now()]
        );

        MonitoringHeartbeatModel::query()->updateOrCreate(
            ['key' => 'schedule'],
            ['payload' => ['source' => 'monitoring:heartbeat'], 'recorded_at' => now()]
        );

        $this->info('Monitoring heartbeat recorded.');

        return self::SUCCESS;
    }
}
