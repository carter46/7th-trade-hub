<?php

namespace App\Services\Reporting;

use App\Models\AnalyticsGaSnapshot;
use App\Models\AnalyticsProvider;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Services\Reporting\Metrics\OpsMetrics;
use App\Services\Reporting\Metrics\PlatformRevenueMetric;

class ReportingService
{
    public function __construct(
        private PlatformRevenueMetric $revenue,
        private OpsMetrics $ops,
    ) {}

    /**
     * Command-center overview payload for a reporting range.
     *
     * @return array<string, mixed>
     */
    public function overview(ReportingRange $range): array
    {
        $revenue = $this->revenue->sum($range);
        $revenueDelta = $this->revenue->deltaPercent($range);
        $fundings = $this->ops->fundingsCount($range);
        $priorFundings = $this->ops->fundingsCount($range->priorPeriod());
        $fundingDelta = $this->percentDelta((float) $fundings, (float) $priorFundings);

        $revenueSeries = $this->revenue->dailySeries($range);
        $usersSeries = $this->ops->usersDailySeries($range);
        $fundingsSeries = $this->ops->fundingsDailySeries($range);

        return [
            'range' => $range->toArray(),
            'pulse' => [
                'revenue' => [
                    'value' => $revenue,
                    'formatted' => '₦'.number_format($revenue, 2),
                    'delta' => $revenueDelta,
                    'delta_label' => 'vs prior period',
                ],
                'visitors' => $this->visitorsPulse(),
                'fundings' => [
                    'value' => $fundings,
                    'formatted' => (string) $fundings,
                    'delta' => $fundingDelta,
                    'delta_label' => 'vs prior period',
                ],
                'pending_kyc' => $this->ops->pendingKyc(),
                'pending_escrows' => $this->ops->pendingEscrows(),
                'escrow_locked_ngn' => $this->ops->lockedEscrowVolume(),
                'support_waiting' => $this->ops->supportWaiting(),
                'pending_listings' => $this->ops->pendingListings(),
            ],
            'growth' => [
                'revenue' => $revenueSeries,
                'users' => $usersSeries,
                'fundings' => $fundingsSeries,
            ],
            'gmv' => $this->ops->gmv($range),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function revenueSection(ReportingRange $range): array
    {
        return [
            'total_ngn' => $this->revenue->sum($range),
            'count' => $this->revenue->count($range),
            'by_type' => $this->revenue->byType($range),
            'delta_percent' => $this->revenue->deltaPercent($range),
            'series' => $this->revenue->dailySeries($range),
            'gmv' => $this->ops->gmv($range),
        ];
    }

    public function platformRevenue(ReportingRange $range): float
    {
        return $this->revenue->sum($range);
    }

    public function revenueDailySeries(ReportingRange $range): array
    {
        return $this->revenue->dailySeries($range);
    }

    /**
     * @return list<array{key: string, label: string, labels: list<string>, values: list<float|null>}>
     */
    public function chartStrip(ReportingRange $range): array
    {
        $revenue = $this->revenue->dailySeries($range);
        $users = $this->ops->usersDailySeries($range);
        $fundings = $this->ops->fundingsDailySeries($range);

        return [
            [
                'key' => 'revenue.daily',
                'label' => 'Revenue (NGN)',
                'labels' => $revenue['labels'],
                'values' => $revenue['values'],
            ],
            [
                'key' => 'users.daily',
                'label' => 'New users',
                'labels' => $users['labels'],
                'values' => $users['values'],
            ],
            [
                'key' => 'fundings.daily',
                'label' => 'Wallet fundings',
                'labels' => $fundings['labels'],
                'values' => $fundings['values'],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentTransactions(int $limit = 10): array
    {
        return Transaction::query()
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Transaction $tx) => [
                'id' => $tx->id,
                'reference' => $tx->reference,
                'user_name' => $tx->user?->name ?? '—',
                'user_initials' => $this->initials($tx->user?->name),
                'amount' => (float) $tx->amount,
                'status' => $tx->status,
                'created_at' => $tx->created_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentAudit(int $limit = 8): array
    {
        return AuditLog::query()
            ->with('admin:id,name,email')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (AuditLog $log) => [
                'action' => $log->action,
                'actor' => $log->admin?->email ?? $log->admin?->name ?? 'system',
                'when' => $log->created_at?->diffForHumans(),
                'severity' => str_contains((string) $log->action, 'suspend')
                    || str_contains((string) $log->action, 'anonym'),
            ])
            ->all();
    }

    /**
     * @return array{enabled: bool, value: float|null, hint: string|null}
     */
    private function visitorsPulse(): array
    {
        $provider = AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_GOOGLE_ANALYTICS);
        if (! $provider->enabled) {
            return ['enabled' => false, 'value' => null, 'hint' => 'Google Analytics disabled'];
        }

        $snap = AnalyticsGaSnapshot::query()
            ->whereDate('captured_at', today())
            ->where('metric', 'sessions')
            ->orderByDesc('captured_at')
            ->first();

        return [
            'enabled' => true,
            'value' => $snap ? (float) ($snap->payload['value'] ?? 0) : null,
            'hint' => $snap ? null : 'Awaiting GA snapshot',
        ];
    }

    private function percentDelta(float $current, float $prior): float
    {
        if (abs($prior) < 0.00001) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $prior) / abs($prior)) * 100, 1);
    }

    private function initials(?string $name): string
    {
        if (! $name) {
            return '?';
        }
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $letters ?: '?';
    }
}
