@extends('layouts.dashboard-admin')

@section('title', 'Audit Logs')

@section('content')
<x-layout.page title="Audit Logs" subtitle="Admin actions and system changes." width="full">
    <x-dashboard.table
        :empty="$logs->isEmpty()"
        empty-title="No audit logs yet"
        empty-description="Admin actions will be recorded here."
        empty-icon="audit"
        striped
    >
        <x-slot:filters>
            <form method="GET" class="flex flex-col sm:flex-row gap-3 max-w-md">
                <div class="flex-1">
                    <x-dashboard.input name="action" type="text" :value="request('action')" placeholder="Filter by action..." />
                </div>
                <x-dashboard.button type="submit" variant="secondary" size="md">Filter</x-dashboard.button>
            </form>
        </x-slot:filters>

        <x-slot:head>
            <x-dashboard.th>Time</x-dashboard.th>
            <x-dashboard.th>Admin</x-dashboard.th>
            <x-dashboard.th>Action</x-dashboard.th>
            <x-dashboard.th>Model</x-dashboard.th>
            <x-dashboard.th>IP</x-dashboard.th>
        </x-slot:head>

        @foreach ($logs as $log)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="text-text-muted text-xs">{{ $log->created_at->format('M j, Y H:i') }}</x-dashboard.td>
                <x-dashboard.td>{{ $log->admin?->email ?? '—' }}</x-dashboard.td>
                <x-dashboard.td class="font-mono text-xs">{{ $log->action }}</x-dashboard.td>
                <x-dashboard.td class="text-xs text-text-muted">{{ $log->model_type ? class_basename($log->model_type).' #'.$log->model_id : '—' }}</x-dashboard.td>
                <x-dashboard.td class="text-xs text-text-muted">{{ $log->ip ?? '—' }}</x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$logs" />
    </x-slot:pagination>
</x-layout.page>
@endsection
