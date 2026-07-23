<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Analytics\AnalyticsServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\AnalyticsProvider;
use App\Models\AuditLog;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Services\Analytics\InternalBusinessProvider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsServiceInterface $analytics,
        private InternalBusinessProvider $business,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $canFinance = $user?->can('finance.manage') ?? false;
        $canSupport = $user?->can('support.manage') ?? false;
        $canCompliance = $user?->can('compliance.manage') ?? false;
        $canAnalytics = $user?->can('analytics.view') ?? false;
        $canCatalog = $user?->can('catalog.manage') ?? false;
        $canSystem = $user?->can('system.manage') ?? false;

        $alerts = ($canFinance || $canSupport || $canCompliance || $canAnalytics)
            ? $this->business->commandCenterAlerts()
            : [];

        $visitors = $this->business->visitorsToday();
        $gaEnabled = AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_GOOGLE_ANALYTICS)->enabled;

        $chartStrip = $canAnalytics ? $this->analytics->chartStrip($user, 7) : [];

        $recentTransactions = $canFinance
            ? Transaction::with('user')->orderByDesc('created_at')->limit(10)->get()
            : collect();

        $recentActivity = $canSystem
            ? AuditLog::with('admin')->orderByDesc('created_at')->limit(8)->get()
            : collect();

        $pendingListings = $canCatalog
            ? (int) ($alerts['pending_listings'] ?? 0)
            : 0;

        return view('dashboard.admin.overview', [
            'greeting' => $this->greeting(),
            'adminName' => $user?->name ?? 'Admin',
            'canFinance' => $canFinance,
            'canSupport' => $canSupport,
            'canCompliance' => $canCompliance,
            'canAnalytics' => $canAnalytics,
            'canCatalog' => $canCatalog,
            'canSystem' => $canSystem,
            'alerts' => $alerts,
            'visitors' => $visitors,
            'gaEnabled' => $gaEnabled,
            'chartStrip' => $chartStrip,
            'recentTransactions' => $recentTransactions,
            'recentActivity' => $recentActivity,
            'pendingListings' => $pendingListings,
            'quickActions' => $this->quickActions($pendingListings),
        ]);
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
     * @return list<array{label: string, href: string, icon: string, description: string|null}>
     */
    private function quickActions(int $pendingListings): array
    {
        $user = auth()->user();

        $actions = [
            [
                'label' => 'Create Service',
                'description' => 'Add a platform service to the catalog.',
                'href' => route('admin.services.create'),
                'icon' => 'listings',
                'permission' => 'catalog.manage',
            ],
            [
                'label' => 'Approve Listings',
                'description' => $pendingListings > 0
                    ? "{$pendingListings} listing(s) awaiting review."
                    : 'Review marketplace submissions.',
                'href' => route('admin.listings', ['status' => 'pending']),
                'icon' => 'inventory',
                'permission' => 'catalog.manage',
            ],
            [
                'label' => 'Review KYC',
                'description' => 'Identity verification queue.',
                'href' => route('admin.kyc', ['status' => 'pending']),
                'icon' => 'kyc',
                'permission' => 'compliance.manage',
            ],
            [
                'label' => 'Review Escrows',
                'description' => 'Held funds awaiting release.',
                'href' => route('admin.escrows'),
                'icon' => 'lock',
                'permission' => 'finance.manage',
            ],
        ];

        return array_values(array_filter(
            $actions,
            fn (array $action): bool => $user?->can($action['permission']) ?? false,
        ));
    }

    public function transactions(): View
    {
        $transactions = Transaction::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.admin.transactions', [
            'transactions' => $transactions,
        ]);
    }

    public function analytics(Request $request): View
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

        $filters = [
            'range' => $request->string('range')->toString() ?: '30d',
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
        ];

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

        return view('dashboard.admin.analytics', [
            'section' => $section,
            'range' => $range,
            'filters' => $filters,
            'data' => $data,
            'gaEnabled' => $gaEnabled,
            'gaConnected' => $gaConnected,
            'productMetrics' => $productMetrics,
            'marketing' => $marketing,
            'sections' => $allowedSections,
        ]);
    }

    public function social(): View
    {
        return view('dashboard.admin.social', [
            'items' => collect(),
        ]);
    }
}
