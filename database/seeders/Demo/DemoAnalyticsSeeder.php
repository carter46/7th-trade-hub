<?php

namespace Database\Seeders\Demo;

use App\Models\AnalyticsKpiSnapshot;
use App\Models\ProductMetricDaily;
use App\Models\UserActivity;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DemoAnalyticsSeeder extends Seeder
{
    public function run(DemoContext $ctx, DemoTimeline $timeline): void
    {
        $routeKeys = [
            'dashboard.marketplace.view',
            'dashboard.wallet.view',
            'dashboard.orders.view',
            'dashboard.kyc.view',
            'dashboard.support.view',
            'discover.marketplace',
            'discover.services',
        ];

        $funnel = [
            'searched', 'filtered', 'added_to_cart', 'started_checkout',
            'abandoned_checkout', 'completed_purchase', 'opened_ticket',
            'viewed_wallet', 'viewed_transaction',
        ];

        foreach ($ctx->members() as $key => $user) {
            if ($key === 'emily') {
                // Near-zero activity by design
                UserActivity::query()->create([
                    'user_id' => $user->id,
                    'action' => 'viewed',
                    'context_key' => 'dashboard.marketplace.view',
                    'occurred_at' => $timeline->daysAgo(0, 18),
                ]);

                continue;
            }

            $bias = in_array($key, ['michael', 'sarah', 'alice'], true) ? 3 : 1;
            $daySpan = $bias > 1 ? 60 : 30;

            for ($d = 0; $d < $daySpan; $d++) {
                if ($d % 7 === 0 || $d % 7 === 6) {
                    // Weekend dip: fewer events
                    if ($d % 3 !== 0) {
                        continue;
                    }
                }

                // Sparse sample for fillers
                if ($bias === 1 && $d % 2 !== 0) {
                    continue;
                }

                $at = $timeline->daysAgo($d, 10 + ($d % 8));
                $route = $routeKeys[($d + $user->id) % count($routeKeys)];
                // Marketplace-heavy bias for traders/sellers
                if ($bias > 1 && $d % 2 === 0) {
                    $route = 'dashboard.marketplace.view';
                }

                UserActivity::query()->create([
                    'user_id' => $user->id,
                    'action' => 'viewed',
                    'context_key' => $route,
                    'meta' => ['demo' => true],
                    'occurred_at' => $at,
                ]);

                if ($d % 5 === 0) {
                    $action = $funnel[$d % count($funnel)];
                    UserActivity::query()->create([
                        'user_id' => $user->id,
                        'action' => $action,
                        'context_key' => $action,
                        'meta' => ['demo' => true],
                        'occurred_at' => $at->copy()->addMinutes(20),
                    ]);
                }

                ProductMetricDaily::query()->updateOrCreate(
                    [
                        'day' => $at->toDateString(),
                        'metric_key' => 'page.'.$route,
                        'dimension' => (string) $user->id,
                    ],
                    ['count' => $bias]
                );
            }
        }

        // KPI snapshots via the same ReportingService definitions as Overview / rollup.
        Artisan::call('analytics:rollup-kpis');

        AnalyticsKpiSnapshot::query()
            ->where('captured_at', '>=', now()->subMinutes(5))
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->each(function (AnalyticsKpiSnapshot $snap): void {
                $meta = is_array($snap->meta) ? $snap->meta : [];
                $meta['demo'] = true;
                $snap->forceFill(['meta' => $meta])->saveQuietly();
            });

        $ctx->note('✓ Analytics activity + KPI snapshots generated from ReportingService');
    }
}
