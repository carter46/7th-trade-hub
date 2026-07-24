@extends('layouts.dashboard-admin')

@section('title', 'Overview')

@section('content')
<div
    class="space-y-12"
    data-command-overview
    data-overview-endpoint="{{ route('admin.overview.panel') }}"
>
    <x-dashboard.command.page-toolbar
        :greeting="($greeting ?? 'Hello').', '.($adminName ?? 'Admin')"
        subtitle="Command center — platform alerts and quick actions."
        :breadcrumb="[['Admin'], ['Overview']]"
        :range="$rangeKey ?? '24h'"
    />

    <div id="command-live" class="space-y-12">
        @include('dashboard.admin.partials.overview-live')
    </div>

    @if (! empty($quickActions))
        <section class="space-y-4">
            <x-dashboard.command.section-label title="Platform Quick Actions" accent="indigo" />
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
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
        </section>
    @endif

    <section class="space-y-4">
        <x-dashboard.command.section-label title="Operations & Health" accent="orange" />
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                @if ($canFinance ?? false)
                    <x-dashboard.command.tx-table
                        :rows="$recentTransactions ?? []"
                        :view-all-url="route('admin.transactions')"
                    />
                @endif
            </div>
            <div class="flex flex-col gap-6 lg:col-span-4">
                @if ($canSystem ?? false)
                    <x-dashboard.command.health-panel
                        :rings="$health['rings'] ?? []"
                        :metrics="$health['metrics'] ?? []"
                        :checked-at="$health['checked_at'] ?? null"
                    />
                    <x-dashboard.command.audit-timeline
                        :entries="$recentAudit ?? []"
                        :console-url="route('admin.audit-logs')"
                    />
                @elseif ($canAnalytics ?? false)
                    <a href="{{ route('admin.analytics') }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-border-default dark:bg-elevated">
                        <p class="text-sm font-bold text-slate-900 dark:text-text-primary">Analytics drill-down</p>
                        <p class="mt-1 text-xs text-slate-500">Traffic, revenue, marketplace, and ops reports with shared ranges.</p>
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-command-overview]');
    if (!root || !window.bindCommandRange) return;
    window.bindCommandRange(root, {
        endpoint: root.dataset.overviewEndpoint,
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
