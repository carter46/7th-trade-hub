@extends('layouts.dashboard-admin')

@section('title', 'Overview')

@section('content')
<div
    class="space-y-8"
    data-command-overview
    data-overview-endpoint="{{ route('admin.overview.panel') }}"
>
    <x-dashboard.command.page-toolbar
        :greeting="($greeting ?? 'Hello').', '.($adminName ?? 'Admin')"
        subtitle="Command center — live platform pulse for the selected range."
        :breadcrumb="[['Admin'], ['Overview']]"
        :range="$rangeKey ?? '7d'"
    />

    <div id="command-live" class="space-y-8">
        @include('dashboard.admin.partials.overview-live')
    </div>

    @if (! empty($quickActions))
        <div>
            <x-dashboard.command.section-label title="Platform Quick Actions" accent="indigo" />
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($quickActions as $action)
                    <x-dashboard.command.quick-action
                        :title="$action['title']"
                        :subtitle="$action['subtitle'] ?? null"
                        :icon="$action['icon']"
                        :href="$action['href']"
                        :accent="$action['accent'] ?? 'emerald'"
                    />
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <x-dashboard.command.section-label title="Operations & Health" accent="amber" />
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 space-y-6">
                @if ($canFinance ?? false)
                    <x-dashboard.command.tx-table
                        :rows="$recentTransactions ?? []"
                        :view-all-url="route('admin.transactions')"
                    />
                @endif
            </div>
            <div class="space-y-6">
                @if ($canSystem ?? false)
                    <x-dashboard.command.health-panel
                        :metrics="$healthMetrics ?? []"
                        :uptime="null"
                    />
                    <x-dashboard.command.audit-timeline
                        :entries="$recentAudit ?? []"
                        :console-url="route('admin.audit-logs')"
                    />
                @elseif ($canAnalytics ?? false)
                    <a href="{{ route('admin.analytics') }}" class="block rounded-2xl border border-border-default bg-elevated p-5 hover:border-brand transition-colors">
                        <p class="text-sm font-bold text-text-primary">Analytics drill-down</p>
                        <p class="mt-1 text-xs text-text-muted">Traffic, revenue, marketplace, and ops reports with shared ranges.</p>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-command-overview]');
    if (!root || !window.bindCommandRange) return;
    const endpoint = root.dataset.overviewEndpoint;
    window.bindCommandRange(root, {
        endpoint,
        onHtml(html) {
            const live = document.getElementById('command-live');
            if (!live) return;
            live.innerHTML = html;
            window.mountCommandCharts?.(live);
        },
    });
});
</script>
@endpush
@endsection
