<?php

namespace App\Services\Reporting\Metrics;

use App\Models\Escrow;
use App\Models\KycSubmission;
use App\Models\Listing;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WalletFunding;
use App\Services\Reporting\ReportingRange;
use Illuminate\Support\Facades\DB;

class OpsMetrics
{
    public function fundingsCount(ReportingRange $range): int
    {
        return (int) WalletFunding::query()
            ->where('status', 'approved')
            ->whereBetween('approved_at', [$range->from, $range->to])
            ->count();
    }

    public function fundingsSum(ReportingRange $range): float
    {
        return round((float) WalletFunding::query()
            ->where('status', 'approved')
            ->whereBetween('approved_at', [$range->from, $range->to])
            ->sum('amount'), 2);
    }

    public function newUsers(ReportingRange $range): int
    {
        return (int) User::query()
            ->whereBetween('created_at', [$range->from, $range->to])
            ->count();
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function fundingsDailySeries(ReportingRange $range): array
    {
        return $this->dailyCountSeries(
            $range,
            fn (string $day) => WalletFunding::query()
                ->where('status', 'approved')
                ->whereDate('approved_at', $day)
                ->count()
        );
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function usersDailySeries(ReportingRange $range): array
    {
        return $this->dailyCountSeries(
            $range,
            fn (string $day) => User::query()->whereDate('created_at', $day)->count()
        );
    }

    public function pendingKyc(): int
    {
        return (int) KycSubmission::query()->where('status', 'pending')->count();
    }

    public function pendingEscrows(): int
    {
        return (int) Escrow::query()->whereIn('status', ['locked', 'disputed'])->count();
    }

    public function lockedEscrowVolume(): float
    {
        return round((float) Escrow::query()
            ->whereIn('status', ['locked', 'disputed'])
            ->sum('amount'), 2);
    }

    public function supportWaiting(): int
    {
        return (int) SupportTicket::query()
            ->whereIn('status', ['open', 'pending', 'awaiting_user'])
            ->count();
    }

    public function pendingListings(): int
    {
        return (int) Listing::query()->where(function ($q) {
            $q->where('status', 'pending_review')
                ->orWhere(function ($inner) {
                    $inner->where('status', 'published')
                        ->whereHas('versions', fn ($v) => $v->where('status', 'pending_review'));
                });
        })->whereNotIn('status', ['archived', 'sold'])->count();
    }

    /** Gross marketplace order totals completed/paid in range. */
    public function gmv(ReportingRange $range): float
    {
        return round((float) Order::query()
            ->where('source', 'marketplace')
            ->whereIn('status', ['completed', 'paid', 'processing'])
            ->whereBetween('created_at', [$range->from, $range->to])
            ->sum(DB::raw('COALESCE(total_amount, amount)')), 2);
    }

    /**
     * @param  callable(string): int|float  $counter
     * @return array{labels: list<string>, values: list<float>}
     */
    private function dailyCountSeries(ReportingRange $range, callable $counter): array
    {
        $labels = [];
        $values = [];
        $cursor = $range->from->copy()->startOfDay();
        $end = $range->to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('M j');
            $values[] = (float) $counter($cursor->toDateString());
            $cursor->addDay();
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
