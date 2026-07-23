@extends('layouts.dashboard-admin')

@section('title', 'Audit Logs')

@section('content')
<x-layout.page
    title="Audit Logs"
    subtitle="Admin actions and system changes."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Audit Logs', null],
    ]"
>
    <x-dashboard.table
        :empty="$logs->isEmpty()"
        empty-title="No audit logs yet"
        empty-description="Admin actions will be recorded here."
        empty-icon="audit"
        striped
    >
        <x-slot:filters>
            <x-dashboard.filter-bar>
                <form method="GET" class="contents flex flex-wrap gap-3 items-end">
                    <div class="min-w-[10rem] flex-1">
                        <x-dashboard.input name="action" type="text" label="Action" :value="$filters['action'] ?? ''" placeholder="e.g. kyc.approved" />
                    </div>
                    <div class="min-w-[10rem]">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Module</label>
                        <select name="module" class="w-full rounded-xl border-border-default bg-elevated text-sm">
                            <option value="">All modules</option>
                            @foreach ($modules as $mod)
                                <option value="{{ $mod }}" @selected(($filters['module'] ?? '') === $mod)>{{ $mod }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[10rem]">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Admin</label>
                        <select name="admin_id" class="w-full rounded-xl border-border-default bg-elevated text-sm">
                            <option value="">All admins</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}" @selected(($filters['admin_id'] ?? null) == $admin->id)>{{ $admin->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-dashboard.input type="date" name="date_from" label="From" :value="$filters['date_from'] ?? ''" />
                    <x-dashboard.input type="date" name="date_to" label="To" :value="$filters['date_to'] ?? ''" />
                    <x-dashboard.button type="submit" variant="secondary" size="md">Filter</x-dashboard.button>
                </form>
            </x-dashboard.filter-bar>
        </x-slot:filters>

        <x-slot:head>
            <x-dashboard.th></x-dashboard.th>
            <x-dashboard.th>Time</x-dashboard.th>
            <x-dashboard.th>Admin</x-dashboard.th>
            <x-dashboard.th>Module</x-dashboard.th>
            <x-dashboard.th>Action</x-dashboard.th>
            <x-dashboard.th>Model</x-dashboard.th>
            <x-dashboard.th>Device</x-dashboard.th>
            <x-dashboard.th>IP</x-dashboard.th>
        </x-slot:head>

        @foreach ($logs as $log)
            <tbody x-data="{ open: false }" class="border-b border-border-default">
                <tr class="hover:bg-muted/50">
                    <x-dashboard.td>
                        <button type="button" class="text-xs text-primary" @click="open = !open" :aria-expanded="open">
                            <span x-text="open ? 'Hide' : 'Details'"></span>
                        </button>
                    </x-dashboard.td>
                    <x-dashboard.td class="text-text-muted text-xs">{{ $log->created_at->format('M j, Y H:i') }}</x-dashboard.td>
                    <x-dashboard.td>{{ $log->admin?->email ?? '—' }}</x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $log->module ?? '—' }}</x-dashboard.td>
                    <x-dashboard.td class="font-mono text-xs">{{ $log->action }}</x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $log->model_type ? class_basename($log->model_type).' #'.$log->model_id : '—' }}</x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $log->device ? $log->device . ' / ' . ($log->browser ?? '') : '—' }}</x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $log->ip ?? '—' }}</x-dashboard.td>
                </tr>
                <tr x-show="open" x-cloak class="bg-muted/30">
                    <td colspan="8" class="px-4 py-3 text-xs text-text-secondary">
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <div class="font-medium text-text-primary mb-1">Reason / correlation</div>
                                <p>Reason: {{ $log->reason ?: '—' }}</p>
                                <p>Correlation: {{ $log->correlation_id ?: '—' }}</p>
                                <p>Request: {{ $log->request_id ?: '—' }}</p>
                            </div>
                            <div>
                                <div class="font-medium text-text-primary mb-1">Changes</div>
                                <pre class="overflow-x-auto rounded-lg bg-elevated p-2 text-[11px]">{{ json_encode(['old' => $log->old_values, 'new' => $log->new_values], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$logs" />
    </x-slot:pagination>
</x-layout.page>
@endsection
