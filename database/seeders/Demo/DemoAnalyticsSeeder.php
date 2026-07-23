<?php

namespace Database\Seeders\Demo;

use App\Models\AnalyticsKpiSnapshot;
use App\Models\Escrow;
use App\Models\KycSubmission;
use App\Models\Listing;
use App\Models\Order;
use App\Models\ProductMetricDaily;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\WalletFunding;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;

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

        // KPI snapshots derived from live facts (current period)
        $now = now();
        $snapshots = [
            ['users.total', 'current', User::role('user')->count()],
            ['listings.active', 'current', Listing::query()->where('is_active', true)->count()],
            ['listings.pending_review', 'current', Listing::query()->where('status', 'pending_review')->count()],
            ['kyc.pending', 'current', KycSubmission::query()->where('status', 'pending')->count()],
            ['escrows.pending', 'current', Escrow::query()->where('status', 'locked')->count()],
            ['support.waiting', 'current', SupportTicket::query()->whereIn('status', ['open', 'pending', 'awaiting_user'])->count()],
            ['tickets.open', 'current', SupportTicket::query()->whereIn('status', ['open', 'pending', 'awaiting_user'])->count()],
            ['tickets.total', 'current', SupportTicket::count()],
            ['sales.total_ngn', 'current', (float) Transaction::query()->where('status', 'completed')->where('type', 'platform_fee')->where('currency', 'NGN')->sum('amount')],
            ['transactions.total', 'current', Transaction::count()],
            ['revenue.today', 'today', (float) Transaction::query()->where('status', 'completed')->where('type', 'platform_fee')->where('currency', 'NGN')->whereDate('created_at', today())->sum('amount')],
            ['fundings.today', 'today', WalletFunding::query()->where('status', 'approved')->whereDate('approved_at', today())->count()],
        ];

        foreach ($snapshots as [$key, $period, $value]) {
            AnalyticsKpiSnapshot::query()->updateOrCreate(
                ['kpi_key' => $key, 'period' => $period],
                ['value' => $value, 'captured_at' => $now, 'meta' => ['demo' => true]]
            );
        }

        $ordersByStatus = Order::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->map(fn ($c) => (int) $c)
            ->all();

        AnalyticsKpiSnapshot::query()->updateOrCreate(
            ['kpi_key' => 'orders.by_status', 'period' => 'current'],
            [
                'value' => array_sum($ordersByStatus),
                'meta' => $ordersByStatus,
                'captured_at' => $now,
            ]
        );

        // Daily series for last 30 days from facts
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $dateKey = $day->toDateString();

            foreach ([
                'users.daily' => User::query()->whereDate('created_at', $dateKey)->count(),
                'revenue.daily' => (float) Transaction::query()
                    ->where('status', 'completed')
                    ->where('type', 'platform_fee')
                    ->where('currency', 'NGN')
                    ->whereDate('created_at', $dateKey)
                    ->sum('amount'),
                'fundings.daily' => WalletFunding::query()
                    ->where('status', 'approved')
                    ->whereDate('approved_at', $dateKey)
                    ->count(),
            ] as $kpiKey => $value) {
                $existing = AnalyticsKpiSnapshot::query()
                    ->where('kpi_key', $kpiKey)
                    ->where('period', 'daily')
                    ->where('meta->day', $dateKey)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'value' => $value,
                        'meta' => ['day' => $dateKey, 'demo' => true],
                        'captured_at' => $day->copy()->endOfDay(),
                    ]);
                } else {
                    AnalyticsKpiSnapshot::query()->create([
                        'kpi_key' => $kpiKey,
                        'period' => 'daily',
                        'value' => $value,
                        'meta' => ['day' => $dateKey, 'demo' => true],
                        'captured_at' => $day->copy()->endOfDay(),
                    ]);
                }
            }
        }

        $ctx->note('✓ Analytics activity + KPI snapshots generated from seeded facts');
    }
}
