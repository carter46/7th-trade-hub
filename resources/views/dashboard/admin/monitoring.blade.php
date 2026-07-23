@extends('layouts.dashboard-admin')

@section('title', 'Monitoring')

@section('content')
<x-layout.page
    title="System Monitoring"
    subtitle="Infrastructure health, heartbeats, and scheduled task status."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Monitoring', null],
    ]"
>
    <x-dashboard.stat-grid>
        <x-dashboard.stats-card
            label="Disk used"
            :value="isset($disk['used_pct']) ? $disk['used_pct'] . '%' : '—'"
            :hint="isset($disk['free_gb']) ? $disk['free_gb'] . ' GB free of ' . ($disk['total_gb'] ?? '?') . ' GB' : null"
            icon="settings"
        />
        <x-dashboard.stats-card
            label="Cache"
            :value="($cacheOk === true) ? 'Healthy' : (($cacheOk === false) ? 'Error' : 'Unknown')"
            icon="settings"
        />
        <x-dashboard.stats-card
            label="Failed jobs"
            :value="$failedJobs !== null ? number_format($failedJobs) : 'N/A'"
            icon="audit"
        />
        <x-dashboard.stats-card
            label="Database size"
            :value="$dbSizeMb !== null ? number_format($dbSizeMb, 1) . ' MB' : 'N/A'"
            icon="transactions"
        />
        <x-dashboard.stats-card
            label="Queue"
            :value="$queueStatus ?? 'N/A'"
            :hint="'Driver: ' . ($queueConnection ?? '—')"
            icon="settings"
        />
        <x-dashboard.stats-card
            label="Mail"
            :value="$mailStatus ?? 'N/A'"
            :hint="'Mailer: ' . ($mailMailer ?? '—')"
            icon="settings"
        />
        <x-dashboard.stats-card
            label="Backup"
            :value="$backupStatus ?? 'N/A'"
            hint="Hostinger backups are managed outside the app"
            icon="settings"
        />
    </x-dashboard.stat-grid>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-dashboard.card variant="solid">
            <h3 class="text-base font-semibold text-text-primary mb-3">Scheduler</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-text-muted">Last cron heartbeat</dt>
                    <dd class="font-medium text-text-primary">{{ $scheduleLastRun?->diffForHumans() ?? 'Never recorded' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-text-muted">Last monitoring run</dt>
                    <dd class="font-medium text-text-primary">{{ $monitoringLastRun?->diffForHumans() ?? 'Never recorded' }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-xs text-text-muted">Run <code class="text-xs">php artisan monitoring:heartbeat</code> via cron every 5 minutes.</p>
        </x-dashboard.card>

        <x-dashboard.card variant="solid">
            <h3 class="text-base font-semibold text-text-primary mb-3">Latest heartbeat payload</h3>
            @php $monitoring = $heartbeats->get('monitoring'); @endphp
            @if ($monitoring?->payload)
                <pre class="overflow-x-auto rounded-xl bg-muted/40 p-3 text-xs text-text-secondary">{{ json_encode($monitoring->payload, JSON_PRETTY_PRINT) }}</pre>
            @else
                <p class="text-sm text-text-muted">No heartbeat recorded yet.</p>
            @endif
        </x-dashboard.card>
    </div>

    <x-dashboard.section title="All heartbeats" class="mt-6">
        <x-dashboard.table :empty="$heartbeats->isEmpty()" empty-title="No heartbeats" striped>
            <x-slot:head>
                <x-dashboard.th>Key</x-dashboard.th>
                <x-dashboard.th>Recorded</x-dashboard.th>
                <x-dashboard.th>Payload</x-dashboard.th>
            </x-slot:head>
            @foreach ($heartbeats as $heartbeat)
                <tr>
                    <x-dashboard.td class="font-mono text-xs">{{ $heartbeat->key }}</x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $heartbeat->recorded_at?->format('M j, Y H:i:s') ?? '—' }}</x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ \Illuminate\Support\Str::limit(json_encode($heartbeat->payload), 120) }}</x-dashboard.td>
                </tr>
            @endforeach
        </x-dashboard.table>
    </x-dashboard.section>
</x-layout.page>
@endsection
