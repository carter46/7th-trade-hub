<?php

namespace App\Services\Analytics;

use App\Models\ProductMetricDaily;
use Illuminate\Support\Collection;

class ProductAnalyticsProvider
{
    /**
     * @return array<string, mixed>
     */
    public function topMetrics(int $days = 30, int $limit = 10): array
    {
        $since = now()->subDays($days)->toDateString();

        $rows = ProductMetricDaily::query()
            ->selectRaw('metric_key, SUM(count) as total')
            ->where('day', '>=', $since)
            ->groupBy('metric_key')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return [
            'period_days' => $days,
            'metrics' => $rows->map(fn ($row) => [
                'metric_key' => $row->metric_key,
                'total' => (int) $row->total,
            ])->all(),
        ];
    }

    /**
     * @return Collection<int, ProductMetricDaily>
     */
    public function dailySeries(string $metricKey, int $days = 30): Collection
    {
        $since = now()->subDays($days)->toDateString();

        return ProductMetricDaily::query()
            ->where('metric_key', $metricKey)
            ->where('day', '>=', $since)
            ->orderBy('day')
            ->get();
    }
}
