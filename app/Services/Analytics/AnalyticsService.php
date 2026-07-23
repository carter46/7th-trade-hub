<?php

namespace App\Services\Analytics;

use App\Contracts\Analytics\AnalyticsServiceInterface;
use App\Models\User;
use Carbon\Carbon;

class AnalyticsService implements AnalyticsServiceInterface
{
    /** @var array<string, string> */
    public const SECTION_PERMISSIONS = [
        'traffic' => 'analytics.view',
        'revenue' => 'finance.manage',
        'marketplace' => 'catalog.manage',
        'services' => 'catalog.manage',
        'escrows' => 'finance.manage',
        'users' => 'users.manage',
        'support' => 'support.manage',
        'kyc' => 'compliance.manage',
        'business' => 'analytics.view',
        'products' => 'catalog.manage',
        'marketing' => 'analytics.view',
    ];

    public function __construct(
        private InternalBusinessProvider $business,
        private ProductAnalyticsProvider $products,
    ) {}

    public function getOverview(?User $user): array
    {
        $overview = [
            'generated_at' => now()->toDateTimeString(),
            'kpis' => [],
            'product_metrics' => [],
            'marketing' => [],
        ];

        if (! $user) {
            return $overview;
        }

        if ($user->can('users.manage') || $user->can('analytics.view')) {
            $overview['kpis']['users_total'] = $this->business->kpis()['users_total'];
        }

        if ($user->can('finance.manage') || $user->can('analytics.view')) {
            $kpis = $this->business->kpis();
            $overview['kpis']['sales_total_ngn'] = $kpis['sales_total_ngn'];
            $overview['kpis']['transactions_total'] = $kpis['transactions_total'];
        }

        if ($user->can('catalog.manage') || $user->can('analytics.view')) {
            $overview['kpis']['listings_active'] = $this->business->kpis()['listings_active'];
        }

        if ($user->can('support.manage') || $user->can('analytics.view')) {
            $kpis = $this->business->kpis();
            $overview['kpis']['tickets_total'] = $kpis['tickets_total'];
            $overview['kpis']['tickets_open'] = $kpis['tickets_open'];
        }

        if ($user->can('analytics.view')) {
            $overview['kpis'] = array_merge($overview['kpis'], [
                'orders_by_status' => $this->business->kpis()['orders_by_status'],
            ]);
            $overview['product_metrics'] = $this->products->topMetrics();
            $overview['marketing'] = $this->business->marketingSnapshots();
        }

        return $overview;
    }

    public function getReport(string $section, array $filters, ?User $user): array
    {
        if (! $user) {
            return ['error' => 'Forbidden'];
        }

        $permission = self::SECTION_PERMISSIONS[$section] ?? null;
        if (! $permission || ! $user->can($permission)) {
            return ['error' => 'Forbidden'];
        }

        $range = $this->parseRange($filters);
        $days = $range['days'];

        if (in_array($section, ['traffic', 'revenue', 'marketplace', 'services', 'escrows', 'users', 'support', 'kyc'], true)) {
            return [
                'section' => $section,
                'range' => $range,
                'data' => $this->business->sectionReport($section, $range),
            ];
        }

        return match ($section) {
            'business' => [
                'section' => 'business',
                'range' => $range,
                'data' => $this->business->kpis($filters['period'] ?? 'current'),
            ],
            'products' => [
                'section' => 'products',
                'range' => $range,
                'data' => $this->products->topMetrics($days),
            ],
            'marketing' => [
                'section' => 'marketing',
                'range' => $range,
                'data' => $this->business->marketingSnapshots($days),
            ],
            default => ['error' => 'Unknown section'],
        };
    }

    /**
     * @return list<string>
     */
    public function allowedSections(User $user): array
    {
        $sections = [];
        foreach (['traffic', 'revenue', 'marketplace', 'services', 'escrows', 'users', 'support', 'kyc'] as $section) {
            $permission = self::SECTION_PERMISSIONS[$section];
            if ($user->can($permission)) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{range: string, from: string, to: string, days: int}
     */
    public function parseRange(array $filters): array
    {
        $range = (string) ($filters['range'] ?? '30d');

        if ($range === 'custom') {
            $from = Carbon::parse($filters['from'] ?? now()->subDays(29))->startOfDay();
            $to = Carbon::parse($filters['to'] ?? now())->endOfDay();
        } else {
            [$from, $to] = match ($range) {
                'today' => [now()->startOfDay(), now()->endOfDay()],
                '7d' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
                '30d' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
                '90d' => [now()->subDays(89)->startOfDay(), now()->endOfDay()],
                default => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            };
        }

        $days = max(1, $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1);

        return [
            'range' => $range,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'days' => $days,
        ];
    }

    /**
     * @return list<array{key: string, label: string, labels: list<string>, values: list<float|null>}>
     */
    public function chartStrip(?User $user, int $days = 7): array
    {
        if (! $user || ! $user->can('analytics.view')) {
            return [];
        }

        return $this->business->chartStrip($days);
    }
}
