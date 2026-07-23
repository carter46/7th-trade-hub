@extends('layouts.dashboard-admin')

@section('title', 'Overview')

@section('content')
@php
    $txs = collect($recentTransactions ?? []);
    $activity = collect($recentActivity ?? []);
    $fmt = function ($value, string $money = '') {
        if ($value === null) {
            return ['value' => '—', 'hint' => 'Awaiting rollup', 'disabled' => true];
        }
        if ($money === 'ngn') {
            return ['value' => '₦' . number_format((float) $value, 2), 'hint' => null, 'disabled' => false];
        }

        return ['value' => number_format((float) $value), 'hint' => null, 'disabled' => false];
    };
    $alertItems = [];
    if ($canFinance ?? false) {
        $rev = $fmt($alerts['revenue_today'] ?? null, 'ngn');
        $alertItems[] = [
            'label' => 'Revenue today',
            'value' => $rev['value'],
            'hint' => $rev['hint'],
            'disabled' => $rev['disabled'],
            'icon' => 'paid',
            'href' => ($canAnalytics ?? false) ? route('admin.analytics', ['section' => 'revenue', 'range' => 'today']) : route('admin.transactions'),
        ];
    }
    if ($canAnalytics ?? false) {
        if (($visitors['connected'] ?? false) && ($visitors['value'] ?? null) !== null) {
            $alertItems[] = [
                'label' => 'Visitors today',
                'value' => number_format($visitors['value']),
                'icon' => 'analytics',
                'href' => route('admin.analytics', ['section' => 'traffic', 'range' => 'today']),
            ];
        } else {
            $alertItems[] = [
                'label' => 'Visitors today',
                'value' => '—',
                'hint' => $visitors['message'] ?? 'Connect GA in Settings',
                'icon' => 'analytics',
                'href' => route('admin.settings'),
                'disabled' => true,
            ];
        }
    }
    if ($canFinance ?? false) {
        $funded = $fmt($alerts['wallets_funded_today'] ?? null);
        $alertItems[] = [
            'label' => 'Wallets funded today',
            'value' => $funded['value'],
            'hint' => $funded['hint'],
            'disabled' => $funded['disabled'],
            'icon' => 'deposit',
            'href' => route('admin.fundings'),
        ];
    }
    if ($canCompliance ?? false) {
        $kyc = $fmt($alerts['pending_kyc'] ?? null);
        $alertItems[] = [
            'label' => 'Pending KYC',
            'value' => $kyc['value'],
            'hint' => $kyc['hint'],
            'disabled' => $kyc['disabled'],
            'icon' => 'kyc',
            'href' => route('admin.kyc', ['status' => 'pending']),
        ];
    }
    if ($canFinance ?? false) {
        $esc = $fmt($alerts['pending_escrows'] ?? null);
        $alertItems[] = [
            'label' => 'Pending escrows',
            'value' => $esc['value'],
            'hint' => $esc['hint'],
            'disabled' => $esc['disabled'],
            'icon' => 'lock',
            'href' => route('admin.escrows'),
        ];
    }
    if ($canSupport ?? false) {
        $sup = $fmt($alerts['support_waiting'] ?? null);
        $alertItems[] = [
            'label' => 'Support waiting',
            'value' => $sup['value'],
            'hint' => $sup['hint'],
            'disabled' => $sup['disabled'],
            'icon' => 'support',
            'href' => route('admin.tickets'),
        ];
    }
@endphp

<x-layout.page
    :title="$greeting . ', ' . ($adminName ?? 'Admin')"
    subtitle="Command center — platform alerts and quick actions."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Overview', null],
    ]"
>
    @if (! empty($alertItems))
        <x-dashboard.stat-grid>
            @foreach ($alertItems as $item)
                @if (! empty($item['disabled']))
                    <x-dashboard.stats-card
                        :label="$item['label']"
                        :value="$item['value']"
                        :hint="$item['hint'] ?? null"
                        :icon="$item['icon']"
                        :href="$item['href']"
                    />
                @else
                    <x-dashboard.chart.kpi
                        :label="$item['label']"
                        :value="$item['value']"
                        :hint="$item['hint'] ?? null"
                        :icon="$item['icon']"
                        :href="$item['href']"
                    />
                @endif
            @endforeach
        </x-dashboard.stat-grid>
    @endif

    @if (($canAnalytics ?? false) && ! empty($chartStrip))
        <x-dashboard.section title="Trends (7 days)" class="mt-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                @foreach ($chartStrip as $chart)
                    <x-dashboard.chart.area
                        :title="$chart['label']"
                        :labels="$chart['labels']"
                        :datasets="[[
                            'label' => $chart['label'],
                            'data' => $chart['values'],
                            'borderColor' => '#6366f1',
                            'backgroundColor' => 'rgba(99, 102, 241, 0.15)',
                        ]]"
                        height="10rem"
                    />
                @endforeach
            </div>
        </x-dashboard.section>
    @endif

    @if (! empty($quickActions))
        <x-dashboard.section title="Quick Actions" class="mt-6">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($quickActions as $action)
                    <a
                        href="{{ $action['href'] }}"
                        class="flex min-h-24 flex-col justify-between rounded-2xl border border-border-default bg-surface px-4 py-4 transition-colors hover:border-primary/40 hover:bg-primary/5 focus-ring"
                    >
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-dashboard.icon :name="$action['icon']" class="h-4 w-4" />
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-text-primary">{{ $action['label'] }}</span>
                            @if (! empty($action['description']))
                                <span class="mt-1 block text-xs text-text-muted">{{ $action['description'] }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </x-dashboard.section>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        @if ($canFinance ?? false)
            <x-dashboard.section title="Recent Transactions">
                <x-slot:actions>
                    <x-dashboard.button :href="route('admin.transactions')" variant="link" size="sm">View all</x-dashboard.button>
                </x-slot:actions>
                <x-dashboard.table
                    :empty="$txs->isEmpty()"
                    empty-title="No transactions yet"
                    empty-description="Run php artisan db:seed to load demo data."
                    empty-icon="transactions"
                    striped
                    :min-height="false"
                >
                    <x-slot:head>
                        <x-dashboard.th>Reference</x-dashboard.th>
                        <x-dashboard.th>User</x-dashboard.th>
                        <x-dashboard.th>Amount</x-dashboard.th>
                        <x-dashboard.th>Status</x-dashboard.th>
                    </x-slot:head>
                    @foreach ($txs->take(6) as $tx)
                        <tr class="hover:bg-muted/50">
                            <x-dashboard.td class="font-mono text-xs">#{{ $tx->reference }}</x-dashboard.td>
                            <x-dashboard.td>{{ $tx->user?->name ?? '—' }}</x-dashboard.td>
                            <x-dashboard.td class="font-medium">{{ number_format($tx->amount, 2) }} {{ $tx->currency }}</x-dashboard.td>
                            <x-dashboard.td><x-dashboard.badge :status="$tx->status" /></x-dashboard.td>
                        </tr>
                    @endforeach
                </x-dashboard.table>
            </x-dashboard.section>
        @endif

        @if ($canSystem ?? false)
            <x-dashboard.section title="Recent Activity">
                <x-slot:actions>
                    <x-dashboard.button :href="route('admin.audit-logs')" variant="link" size="sm">Audit logs</x-dashboard.button>
                </x-slot:actions>
                <x-dashboard.table
                    :empty="$activity->isEmpty()"
                    empty-title="No activity yet"
                    empty-icon="audit"
                    striped
                    :min-height="false"
                >
                    <x-slot:head>
                        <x-dashboard.th>Time</x-dashboard.th>
                        <x-dashboard.th>Action</x-dashboard.th>
                        <x-dashboard.th>Admin</x-dashboard.th>
                    </x-slot:head>
                    @foreach ($activity as $log)
                        <tr class="hover:bg-muted/50">
                            <x-dashboard.td class="text-xs text-text-muted">{{ $log->created_at->diffForHumans() }}</x-dashboard.td>
                            <x-dashboard.td class="font-mono text-xs">{{ $log->action }}</x-dashboard.td>
                            <x-dashboard.td class="text-xs">{{ $log->admin?->email ?? '—' }}</x-dashboard.td>
                        </tr>
                    @endforeach
                </x-dashboard.table>
            </x-dashboard.section>
        @endif
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
        @if ($canAnalytics ?? false)
            <a href="{{ route('admin.analytics') }}" class="rounded-2xl border border-border-default bg-elevated p-5 hover:border-primary/40 transition-colors focus-ring">
                <p class="text-sm font-semibold text-text-primary">Analytics drill-down</p>
                <p class="mt-1 text-xs text-text-muted">Traffic, revenue, marketplace, support, and KYC reports with range filters.</p>
            </a>
        @endif
        @if ($canSystem ?? false)
            <a href="{{ route('admin.monitoring') }}" class="rounded-2xl border border-border-default bg-elevated p-5 hover:border-primary/40 transition-colors focus-ring">
                <p class="text-sm font-semibold text-text-primary">System monitoring</p>
                <p class="mt-1 text-xs text-text-muted">Heartbeats, disk, cache, failed jobs, and database size.</p>
            </a>
        @endif
    </div>
</x-layout.page>
@endsection
