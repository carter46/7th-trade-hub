@extends('layouts.dashboard-admin')

@section('title', 'Analytics')

@section('content')
@php
    $section = $section ?? 'revenue';
    $rangeKey = $range['range'] ?? ($filters['range'] ?? '30d');
    $queryBase = ['section' => $section, 'range' => $rangeKey];
    if ($rangeKey === 'custom') {
        $queryBase['from'] = $range['from'] ?? null;
        $queryBase['to'] = $range['to'] ?? null;
    }
    $sectionLabels = [
        'traffic' => 'Traffic',
        'revenue' => 'Revenue',
        'marketplace' => 'Marketplace',
        'services' => 'Services',
        'escrows' => 'Escrows',
        'users' => 'Users',
        'support' => 'Support',
        'kyc' => 'KYC',
    ];
@endphp

<x-layout.page
    title="Analytics"
    subtitle="Drill-down reports by section and date range."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Analytics', null],
    ]"
>
    <x-dashboard.card class="mb-4">
        <form method="GET" action="{{ route('admin.analytics') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="section" value="{{ $section }}">
            <div>
                <label class="mb-1 block text-xs font-medium text-text-muted">Range</label>
                <select name="range" class="rounded-xl border-border-default bg-elevated text-sm" onchange="this.form.querySelector('[data-custom-range]')?.classList.toggle('hidden', this.value !== 'custom')">
                    @foreach (['today' => 'Today', '7d' => '7 days', '30d' => '30 days', '90d' => '90 days', 'custom' => 'Custom'] as $key => $label)
                        <option value="{{ $key }}" @selected($rangeKey === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div data-custom-range class="flex gap-2 {{ $rangeKey === 'custom' ? '' : 'hidden' }}">
                <x-dashboard.input type="date" name="from" label="From" :value="$range['from'] ?? ''" />
                <x-dashboard.input type="date" name="to" label="To" :value="$range['to'] ?? ''" />
            </div>
            <x-dashboard.button type="submit" variant="secondary" size="md">Apply</x-dashboard.button>
        </form>
    </x-dashboard.card>

    <x-dashboard.ajax-tabs
        :active="$section"
        :tabs="collect($sections ?? [])->map(fn ($s) => [
            'id' => $s,
            'label' => $sectionLabels[$s] ?? ucfirst($s),
            'href' => route('admin.analytics', array_merge($queryBase, ['section' => $s])),
        ])->all()"
        class="mb-6"
    />

    @if ($section === 'traffic')
        <x-dashboard.section title="Marketing — Traffic">
            @if (! ($gaEnabled ?? false) || ! ($gaConnected ?? false) || ! ($data['ga_connected'] ?? false))
                <x-dashboard.card variant="solid">
                    <p class="text-sm text-text-secondary">{{ $data['message'] ?? 'Connect GA in Settings' }}. Open <a href="{{ route('admin.settings') }}" class="text-primary hover:underline">Settings → Analytics</a>.</p>
                </x-dashboard.card>
            @else
                <x-dashboard.stat-grid>
                    <x-dashboard.chart.kpi label="Sessions (range)" :value="number_format($data['totals']['sessions'] ?? 0)" icon="analytics" :href="route('admin.analytics', ['section' => 'traffic', 'range' => $rangeKey])" />
                </x-dashboard.stat-grid>
                @if (! empty($marketing))
                    <x-dashboard.chart.table
                        class="mt-4"
                        title="GA snapshots"
                        :columns="['Metric', 'Period', 'Value']"
                        :rows="collect($marketing)->map(fn ($row) => [
                            $row['metric'],
                            ($row['period_start'] ?? '') . ' — ' . ($row['period_end'] ?? ''),
                            $row['payload']['value'] ?? '—',
                        ])->all()"
                    />
                @endif
            @endif
        </x-dashboard.section>
    @elseif ($section === 'revenue')
        <x-dashboard.section title="Business — Revenue">
            <x-dashboard.stat-grid>
                <x-dashboard.chart.kpi label="Total NGN" :value="'₦' . number_format($data['total_ngn'] ?? 0, 2)" icon="paid" :href="route('admin.transactions')" />
                <x-dashboard.chart.kpi label="Transactions" :value="number_format($data['count'] ?? 0)" icon="transactions" :href="route('admin.transactions')" />
            </x-dashboard.stat-grid>
            @if (! empty($data['by_type']))
                <x-dashboard.chart.bar
                    class="mt-4"
                    title="Revenue by type"
                    :labels="collect($data['by_type'])->pluck('type')->all()"
                    :datasets="[[
                        'label' => 'Total NGN',
                        'data' => collect($data['by_type'])->pluck('total')->all(),
                        'backgroundColor' => '#6366f1',
                    ]]"
                />
            @endif
        </x-dashboard.section>
    @elseif ($section === 'marketplace')
        <x-dashboard.section title="Business — Marketplace">
            <x-dashboard.stat-grid>
                <x-dashboard.chart.kpi label="Active listings" :value="number_format($data['listings_active'] ?? 0)" icon="listings" :href="route('admin.listings')" />
                <x-dashboard.chart.kpi label="New listings" :value="number_format($data['listings_new'] ?? 0)" icon="inventory" :href="route('admin.listings')" />
            </x-dashboard.stat-grid>
        </x-dashboard.section>
        @if (! empty($productMetrics['metrics']))
            <x-dashboard.section title="Product — Activity" class="mt-6">
                <x-dashboard.chart.bar
                    title="Top product metrics"
                    :labels="collect($productMetrics['metrics'])->pluck('metric_key')->all()"
                    :datasets="[[
                        'label' => 'Events',
                        'data' => collect($productMetrics['metrics'])->pluck('total')->all(),
                        'backgroundColor' => '#8b5cf6',
                    ]]"
                />
            </x-dashboard.section>
        @endif
    @elseif ($section === 'services')
        <x-dashboard.section title="Business — Services">
            <x-dashboard.stat-grid>
                <x-dashboard.chart.kpi label="Service orders" :value="number_format($data['service_orders'] ?? 0)" icon="listings" :href="route('admin.transactions')" />
                <x-dashboard.chart.kpi label="Platform transactions" :value="number_format($data['platform_transactions'] ?? 0)" icon="transactions" :href="route('admin.transactions')" />
            </x-dashboard.stat-grid>
        </x-dashboard.section>
    @elseif ($section === 'escrows')
        <x-dashboard.section title="Business — Escrows">
            <x-dashboard.stat-grid>
                <x-dashboard.chart.kpi label="Pending" :value="number_format($data['pending'] ?? 0)" icon="lock" :href="route('admin.escrows')" />
                <x-dashboard.chart.kpi label="Opened in range" :value="number_format($data['opened_in_range'] ?? 0)" icon="orders" :href="route('admin.escrows')" />
                <x-dashboard.chart.kpi label="Released in range" :value="number_format($data['released_in_range'] ?? 0)" icon="paid" :href="route('admin.escrows')" />
            </x-dashboard.stat-grid>
        </x-dashboard.section>
    @elseif ($section === 'users')
        <x-dashboard.section title="Business — Users">
            <x-dashboard.stat-grid>
                <x-dashboard.chart.kpi label="Total users" :value="number_format($data['total'] ?? 0)" icon="group" :href="route('admin.users')" />
                <x-dashboard.chart.kpi label="New in range" :value="number_format($data['new_in_range'] ?? 0)" icon="group" :href="route('admin.users')" />
                <x-dashboard.chart.kpi label="Verified" :value="number_format($data['verified'] ?? 0)" icon="verified" :href="route('admin.users')" />
            </x-dashboard.stat-grid>
            @if (! empty($data['daily_signups']['labels']))
                <x-dashboard.chart.line
                    class="mt-4"
                    title="Daily signups"
                    :labels="$data['daily_signups']['labels']"
                    :datasets="[[
                        'label' => 'Signups',
                        'data' => $data['daily_signups']['values'],
                        'borderColor' => '#6366f1',
                    ]]"
                />
            @endif
        </x-dashboard.section>
    @elseif ($section === 'support')
        <x-dashboard.section title="Business — Support">
            <x-dashboard.stat-grid>
                <x-dashboard.chart.kpi label="Open / waiting" :value="number_format($data['open'] ?? 0)" icon="support" :href="route('admin.tickets')" />
                <x-dashboard.chart.kpi label="Created in range" :value="number_format($data['created_in_range'] ?? 0)" icon="support" :href="route('admin.tickets')" />
            </x-dashboard.stat-grid>
            @if (! empty($data['by_status']))
                <x-dashboard.chart.donut
                    class="mt-4"
                    title="Tickets by status"
                    :labels="array_keys($data['by_status'])"
                    :values="array_values($data['by_status'])"
                />
            @endif
        </x-dashboard.section>
    @elseif ($section === 'kyc')
        <x-dashboard.section title="Business — KYC">
            <x-dashboard.stat-grid>
                <x-dashboard.chart.kpi label="Pending" :value="number_format($data['pending'] ?? 0)" icon="kyc" :href="route('admin.kyc', ['status' => 'pending'])" />
                <x-dashboard.chart.kpi label="Approved in range" :value="number_format($data['approved_in_range'] ?? 0)" icon="verified" :href="route('admin.kyc', ['status' => 'approved'])" />
                <x-dashboard.chart.kpi label="Rejected in range" :value="number_format($data['rejected_in_range'] ?? 0)" icon="kyc" :href="route('admin.kyc', ['status' => 'rejected'])" />
            </x-dashboard.stat-grid>
        </x-dashboard.section>
    @endif
</x-layout.page>
@endsection
