<?php

namespace App\Console\Commands;

use App\Models\ProductMetricDaily;
use App\Models\ProductMetricMonthly;
use App\Models\UserActivity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyticsPruneActivity extends Command
{
    protected $signature = 'analytics:prune-activity';

    protected $description = 'Prune user_activity older than 90 days and rollup daily metrics to monthly';

    public function handle(): int
    {
        $cutoff = now()->subDays(90);

        $deleted = UserActivity::query()
            ->where('occurred_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} user_activity rows older than 90 days.");

        $oldDaily = ProductMetricDaily::query()
            ->where('day', '<', now()->subDays(60)->toDateString())
            ->get();

        $rolled = 0;
        foreach ($oldDaily->groupBy(fn ($row) => $row->day->format('Y-m').'|'.$row->metric_key.'|'.($row->dimension ?? '')) as $group) {
            $first = $group->first();
            $month = $first->day->format('Y-m');
            ProductMetricMonthly::query()->updateOrCreate(
                [
                    'month' => $month,
                    'metric_key' => $first->metric_key,
                    'dimension' => $first->dimension,
                ],
                ['count' => (int) $group->sum('count')]
            );
            $rolled++;
        }

        ProductMetricDaily::query()
            ->where('day', '<', now()->subDays(60)->toDateString())
            ->delete();

        $this->info("Rolled up {$rolled} daily metric groups to monthly.");

        return self::SUCCESS;
    }
}
