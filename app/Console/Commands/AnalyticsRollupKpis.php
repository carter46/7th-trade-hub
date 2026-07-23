<?php

namespace App\Console\Commands;

use App\Models\AnalyticsKpiSnapshot;
use App\Models\Escrow;
use App\Models\KycSubmission;
use App\Models\Listing;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletFunding;
use Illuminate\Console\Command;

class AnalyticsRollupKpis extends Command
{
    protected $signature = 'analytics:rollup-kpis';

    protected $description = 'Upsert analytics_kpi_snapshots for common KPIs and prune old rows';

    public function handle(): int
    {
        $now = now();
        $periods = [
            'current' => null,
            'today' => today(),
            '7d' => now()->subDays(6),
            '30d' => now()->subDays(29),
        ];

        $pendingListings = Listing::query()->where(function ($q) {
            $q->where('status', 'pending_review')
                ->orWhere(function ($inner) {
                    $inner->where('status', 'published')
                        ->whereHas('versions', fn ($v) => $v->where('status', 'pending_review'));
                });
        })->whereNotIn('status', ['archived', 'sold'])->count();

        foreach ($periods as $period => $since) {
            $this->upsertSnapshot('users.total', $period, User::count(), $now);
            $this->upsertSnapshot('listings.active', $period, Listing::where('is_active', true)->count(), $now);
            $this->upsertSnapshot('listings.pending_review', $period, $pendingListings, $now);
            $this->upsertSnapshot('kyc.pending', $period, KycSubmission::where('status', 'pending')->count(), $now);
            $this->upsertSnapshot('escrows.pending', $period, Escrow::where('status', 'locked')->count(), $now);
            $this->upsertSnapshot('support.waiting', $period, SupportTicket::whereIn('status', ['open', 'pending', 'awaiting_user'])->count(), $now);
            $this->upsertSnapshot('tickets.open', $period, SupportTicket::whereIn('status', ['open', 'pending', 'awaiting_user'])->count(), $now);
            $this->upsertSnapshot('tickets.total', $period, SupportTicket::count(), $now);

            $salesQuery = Transaction::query()
                ->where('status', 'completed')
                ->where('currency', 'NGN')
                ->where('amount', '>', 0);
            if ($since) {
                $salesQuery->where('created_at', '>=', $since->copy()->startOfDay());
            }
            $this->upsertSnapshot('sales.total_ngn', $period, (float) $salesQuery->sum('amount'), $now);

            $txQuery = Transaction::query();
            if ($since) {
                $txQuery->where('created_at', '>=', $since->copy()->startOfDay());
            }
            $this->upsertSnapshot('transactions.total', $period, $txQuery->count(), $now);
        }

        $this->upsertSnapshot('revenue.today', 'today', (float) Transaction::query()
            ->where('status', 'completed')
            ->where('currency', 'NGN')
            ->where('amount', '>', 0)
            ->whereDate('created_at', today())
            ->sum('amount'), $now);

        $this->upsertSnapshot('fundings.today', 'today', WalletFunding::query()
            ->where('status', 'approved')
            ->whereDate('approved_at', today())
            ->count(), $now);

        $ordersByStatus = Order::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->map(fn ($c) => (int) $c)
            ->all();

        $this->upsertSnapshot('orders.by_status', 'current', array_sum($ordersByStatus), $now, $ordersByStatus);

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $dateKey = $day->toDateString();

            $this->upsertSnapshot('revenue.daily', 'daily', (float) Transaction::query()
                ->where('status', 'completed')
                ->where('currency', 'NGN')
                ->where('amount', '>', 0)
                ->whereDate('created_at', $dateKey)
                ->sum('amount'), $day->copy()->endOfDay(), ['day' => $dateKey]);

            $this->upsertSnapshot('users.daily', 'daily', User::whereDate('created_at', $dateKey)->count(), $day->copy()->endOfDay(), ['day' => $dateKey]);

            $this->upsertSnapshot('fundings.daily', 'daily', WalletFunding::query()
                ->where('status', 'approved')
                ->whereDate('approved_at', $dateKey)
                ->count(), $day->copy()->endOfDay(), ['day' => $dateKey]);
        }

        $pruned = AnalyticsKpiSnapshot::query()
            ->where('captured_at', '<', now()->subDays(90))
            ->delete();

        $this->info('KPI snapshots rolled up.'.($pruned ? " Pruned {$pruned} old rows." : ''));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function upsertSnapshot(string $key, string $period, float|int $value, $capturedAt, array $meta = []): void
    {
        $query = AnalyticsKpiSnapshot::query()
            ->where('kpi_key', $key)
            ->where('period', $period);

        if ($period === 'daily' && isset($meta['day'])) {
            $query->where('meta->day', $meta['day']);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->update([
                'value' => $value,
                'meta' => $meta ?: null,
                'captured_at' => $capturedAt,
            ]);

            return;
        }

        AnalyticsKpiSnapshot::create([
            'kpi_key' => $key,
            'period' => $period,
            'value' => $value,
            'meta' => $meta ?: null,
            'captured_at' => $capturedAt,
        ]);
    }
}
