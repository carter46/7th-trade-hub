<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Analytics\AnalyticsServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\AnalyticsProvider;
use App\Services\Analytics\InternalBusinessProvider;
use App\Services\Reporting\ReportingRange;
use App\Services\Reporting\ReportingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsServiceInterface $analytics,
        private InternalBusinessProvider $business,
        private ReportingService $reporting,
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $range = $this->resolveOverviewRange($request);
        $payload = $this->overviewPayload($range, $user);

        return view('dashboard.admin.overview', $payload);
    }

    public function overviewPanel(Request $request): Response
    {
        $user = auth()->user();
        $range = $this->resolveOverviewRange($request, persist: true);
        $payload = $this->overviewPayload($range, $user);

        return response()
            ->view('dashboard.admin.partials.overview-live', $payload)
            ->header('Cache-Control', 'no-store');
    }

    public function analytics(Request $request): View|Response
    {
        $user = auth()->user();
        $allowedSections = $this->analytics->allowedSections($user);
        if ($allowedSections === []) {
            abort(403);
        }

        $section = $request->string('section')->toString() ?: ($allowedSections[0] ?? 'revenue');
        if (! in_array($section, $allowedSections, true)) {
            abort(403);
        }

        $sessionKey = "admin.analytics.range.{$section}";
        $filters = [
            'range' => $request->string('range')->toString()
                ?: (string) session($sessionKey, '30d'),
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
        ];

        if ($request->filled('range') || $request->boolean('persist_range')) {
            session([$sessionKey => $filters['range']]);
        }

        $report = $this->analytics->getReport($section, $filters, $user);
        if (($report['error'] ?? null) === 'Forbidden') {
            abort(403);
        }

        $range = $report['range'] ?? $this->analytics->parseRange($filters);
        $data = $report['data'] ?? [];
        $gaProvider = AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_GOOGLE_ANALYTICS);
        $gaEnabled = $gaProvider->enabled;
        $gaConnected = $gaEnabled && $gaProvider->status === 'connected';

        $productMetrics = [];
        if ($section === 'marketplace' && $user?->can('catalog.manage')) {
            $productMetrics = app(\App\Services\Analytics\ProductAnalyticsProvider::class)
                ->topMetrics((int) ($range['days'] ?? 30));
        }

        $marketing = [];
        if ($section === 'traffic' && $user?->can('analytics.view') && $gaConnected) {
            $marketing = $this->business->marketingSnapshots((int) ($range['days'] ?? 7));
        }

        $payload = [
            'section' => $section,
            'range' => $range,
            'filters' => $filters,
            'data' => $data,
            'gaEnabled' => $gaEnabled,
            'gaConnected' => $gaConnected,
            'productMetrics' => $productMetrics,
            'marketing' => $marketing,
            'sections' => $allowedSections,
            'greeting' => $this->greeting(),
            'adminName' => $user?->name ?? 'Admin',
        ];

        if ($request->headers->get('X-Dashboard-Tab') === '1' || $request->boolean('partial')) {
            return response()
                ->view('dashboard.admin.partials.analytics-report', $payload)
                ->header('Cache-Control', 'no-store');
        }

        return view('dashboard.admin.analytics', $payload);
    }

    public function transactions(): View
    {
        $transactions = \App\Models\Transaction::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.admin.transactions', [
            'transactions' => $transactions,
        ]);
    }

    public function social(): View
    {
        return view('dashboard.admin.social', [
            'items' => collect(),
        ]);
    }

    private function resolveOverviewRange(Request $request, bool $persist = true): ReportingRange
    {
        $input = [
            'range' => $request->string('range')->toString()
                ?: (string) session('admin.overview.range', '7d'),
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
        ];

        $range = ReportingRange::fromInput($input, '7d');

        if ($persist && ($request->filled('range') || $request->boolean('persist_range') || ! session()->has('admin.overview.range'))) {
            session(['admin.overview.range' => $range->key]);
        }

        return $range;
    }

    /**
     * @return array<string, mixed>
     */
    private function overviewPayload(ReportingRange $range, $user): array
    {
        $canFinance = $user?->can('finance.manage') ?? false;
        $canSupport = $user?->can('support.manage') ?? false;
        $canCompliance = $user?->can('compliance.manage') ?? false;
        $canAnalytics = $user?->can('analytics.view') ?? false;
        $canCatalog = $user?->can('catalog.manage') ?? false;
        $canSystem = $user?->can('system.manage') ?? false;

        $overview = ($canFinance || $canSupport || $canCompliance || $canAnalytics || $canCatalog)
            ? $this->reporting->overview($range)
            : ['pulse' => [], 'growth' => [], 'range' => $range->toArray()];

        $pulse = $overview['pulse'] ?? [];
        $pendingListings = (int) ($pulse['pending_listings'] ?? 0);

        return [
            'greeting' => $this->greeting(),
            'adminName' => $user?->name ?? 'Admin',
            'canFinance' => $canFinance,
            'canSupport' => $canSupport,
            'canCompliance' => $canCompliance,
            'canAnalytics' => $canAnalytics,
            'canCatalog' => $canCatalog,
            'canSystem' => $canSystem,
            'rangeKey' => $range->key,
            'rangeMeta' => $range->toArray(),
            'pulseItems' => $this->buildPulseItems($pulse, $canFinance, $canAnalytics, $canCompliance, $canSupport, $canCatalog),
            'growth' => $overview['growth'] ?? [],
            'distribution' => $this->buildDistribution($overview['growth'] ?? []),
            'recentTransactions' => $canFinance ? $this->reporting->recentTransactions(8) : [],
            'recentAudit' => $canSystem ? $this->reporting->recentAudit(8) : [],
            'healthMetrics' => $canSystem ? [
                ['label' => 'Monitoring', 'value' => 'Open console', 'ok' => true],
            ] : [],
            'quickActions' => $this->quickActions($pendingListings),
            'pendingListings' => $pendingListings,
        ];
    }

    /**
     * @param  array<string, mixed>  $pulse
     * @return list<array<string, mixed>>
     */
    private function buildPulseItems(
        array $pulse,
        bool $canFinance,
        bool $canAnalytics,
        bool $canCompliance,
        bool $canSupport,
        bool $canCatalog,
    ): array {
        $items = [];

        if ($canFinance) {
            $rev = $pulse['revenue'] ?? [];
            $items[] = [
                'label' => 'Revenue',
                'value' => $rev['formatted'] ?? '₦0.00',
                'accent' => 'emerald',
                'delta' => $rev['delta'] ?? null,
                'delta_label' => $rev['delta_label'] ?? 'vs prior period',
                'hint' => 'in selected range',
                'href' => $canAnalytics
                    ? route('admin.analytics', ['section' => 'revenue', 'range' => request('range', session('admin.overview.range', '7d'))])
                    : route('admin.transactions'),
            ];
        }

        if ($canAnalytics) {
            $visitors = $pulse['visitors'] ?? [];
            if (($visitors['enabled'] ?? false) && ($visitors['value'] ?? null) !== null) {
                $items[] = [
                    'label' => 'Visitors',
                    'value' => number_format((float) $visitors['value']),
                    'accent' => 'blue',
                    'hint' => 'today',
                    'href' => route('admin.analytics', ['section' => 'traffic', 'range' => 'today']),
                ];
            } else {
                $items[] = [
                    'label' => 'Visitors',
                    'value' => '—',
                    'accent' => 'blue',
                    'hint' => $visitors['hint'] ?? 'Connect GA in Settings',
                    'href' => route('admin.settings'),
                ];
            }
        }

        if ($canFinance) {
            $fundings = $pulse['fundings'] ?? [];
            $items[] = [
                'label' => 'Fundings',
                'value' => $fundings['formatted'] ?? '0',
                'accent' => 'indigo',
                'delta' => $fundings['delta'] ?? null,
                'delta_label' => $fundings['delta_label'] ?? 'vs prior period',
                'hint' => 'in selected range',
                'href' => route('admin.fundings'),
            ];
        }

        if ($canCompliance) {
            $items[] = [
                'label' => 'Pending KYC',
                'value' => number_format((int) ($pulse['pending_kyc'] ?? 0)),
                'accent' => 'amber',
                'hint' => 'queue',
                'href' => route('admin.kyc', ['status' => 'pending']),
            ];
        }

        if ($canFinance) {
            $items[] = [
                'label' => 'Pending escrows',
                'value' => number_format((int) ($pulse['pending_escrows'] ?? 0)),
                'accent' => 'indigo',
                'hint' => '₦'.number_format((float) ($pulse['escrow_locked_ngn'] ?? 0), 0).' locked',
                'href' => route('admin.escrows'),
            ];
        }

        if ($canSupport) {
            $items[] = [
                'label' => 'Support waiting',
                'value' => number_format((int) ($pulse['support_waiting'] ?? 0)),
                'accent' => 'orange',
                'hint' => 'open queue',
                'href' => route('admin.tickets'),
            ];
        } elseif ($canCatalog) {
            $items[] = [
                'label' => 'Pending listings',
                'value' => number_format((int) ($pulse['pending_listings'] ?? 0)),
                'accent' => 'amber',
                'hint' => 'review queue',
                'href' => route('admin.listings', ['status' => 'pending']),
            ];
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $growth
     * @return list<array<string, mixed>>
     */
    private function buildDistribution(array $growth): array
    {
        $users = array_sum(array_map('floatval', $growth['users']['values'] ?? []));
        $fundings = array_sum(array_map('floatval', $growth['fundings']['values'] ?? []));
        $revenueDays = count(array_filter($growth['revenue']['values'] ?? [], fn ($v) => (float) $v > 0));
        $total = max(1.0, $users + $fundings + $revenueDays);

        $slices = [
            [
                'label' => 'New users',
                'value' => $users,
                'percent' => round(($users / $total) * 100).'%',
                'color' => '#3b82f6',
            ],
            [
                'label' => 'Fundings',
                'value' => $fundings,
                'percent' => round(($fundings / $total) * 100).'%',
                'color' => '#6366f1',
            ],
            [
                'label' => 'Revenue days',
                'value' => $revenueDays,
                'percent' => round(($revenueDays / $total) * 100).'%',
                'color' => '#10b981',
            ],
        ];

        return array_values(array_filter($slices, fn ($s) => (float) $s['value'] > 0));
    }

    private function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
    }

    /**
     * @return list<array{title: string, subtitle: string|null, href: string, icon: string, accent: string}>
     */
    private function quickActions(int $pendingListings): array
    {
        $user = auth()->user();

        $actions = [
            [
                'title' => 'Create Service',
                'subtitle' => 'Add a platform service to the catalog.',
                'href' => route('admin.services.create'),
                'icon' => 'listings',
                'accent' => 'emerald',
                'permission' => 'catalog.manage',
            ],
            [
                'title' => 'Approve Listings',
                'subtitle' => $pendingListings > 0
                    ? "{$pendingListings} listing(s) awaiting review."
                    : 'Review marketplace submissions.',
                'href' => route('admin.listings', ['status' => 'pending']),
                'icon' => 'inventory',
                'accent' => 'blue',
                'permission' => 'catalog.manage',
            ],
            [
                'title' => 'Review KYC',
                'subtitle' => 'Identity verification queue.',
                'href' => route('admin.kyc', ['status' => 'pending']),
                'icon' => 'kyc',
                'accent' => 'amber',
                'permission' => 'compliance.manage',
            ],
            [
                'title' => 'Review Escrows',
                'subtitle' => 'Held funds awaiting release.',
                'href' => route('admin.escrows'),
                'icon' => 'lock',
                'accent' => 'indigo',
                'permission' => 'finance.manage',
            ],
        ];

        return array_values(array_filter(
            $actions,
            fn (array $action): bool => $user?->can($action['permission']) ?? false,
        ));
    }
}
