<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonitoringHeartbeat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(): View
    {
        $heartbeats = MonitoringHeartbeat::query()
            ->orderByDesc('recorded_at')
            ->get()
            ->keyBy('key');

        $diskTotal = @disk_total_space(base_path()) ?: 0;
        $diskFree = @disk_free_space(base_path()) ?: 0;
        $diskUsedPct = $diskTotal > 0 ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 1) : null;

        $cacheOk = $this->cacheHealthy();
        $failedJobs = $this->failedJobsCount();
        $dbSizeMb = $this->databaseSizeMb();
        $scheduleHeartbeat = $heartbeats->get('schedule');
        $monitoringHeartbeat = $heartbeats->get('monitoring');

        return view('dashboard.admin.monitoring', [
            'heartbeats' => $heartbeats,
            'disk' => [
                'total_gb' => $diskTotal > 0 ? round($diskTotal / 1073741824, 2) : null,
                'free_gb' => $diskFree > 0 ? round($diskFree / 1073741824, 2) : null,
                'used_pct' => $diskUsedPct,
            ],
            'cacheOk' => $cacheOk,
            'failedJobs' => $failedJobs,
            'dbSizeMb' => $dbSizeMb,
            'scheduleLastRun' => $scheduleHeartbeat?->recorded_at,
            'monitoringLastRun' => $monitoringHeartbeat?->recorded_at,
            'queueConnection' => config('queue.default'),
            'mailMailer' => config('mail.default'),
            'queueStatus' => config('queue.default') === 'sync' ? 'N/A (sync)' : null,
            'mailStatus' => 'N/A',
            'backupStatus' => 'N/A',
        ]);
    }

    private function cacheHealthy(): ?bool
    {
        try {
            $key = 'monitoring.ping';
            Cache::put($key, 'ok', 60);

            return Cache::get($key) === 'ok';
        } catch (\Throwable) {
            return null;
        }
    }

    private function failedJobsCount(): ?int
    {
        try {
            if (! Schema::hasTable('failed_jobs')) {
                return null;
            }

            return (int) DB::table('failed_jobs')->count();
        } catch (\Throwable) {
            return null;
        }
    }

    private function databaseSizeMb(): ?float
    {
        try {
            if (DB::getDriverName() !== 'mysql') {
                return null;
            }

            $database = DB::getDatabaseName();
            $row = DB::selectOne(
                'SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                 FROM information_schema.tables WHERE table_schema = ?',
                [$database]
            );

            return isset($row->size_mb) ? (float) $row->size_mb : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
