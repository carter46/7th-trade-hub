@extends('layouts.dashboard-admin')

@section('title', 'Audit Logs')

@section('content')
<x-layout.page title="Audit Logs" subtitle="Admin actions and system changes." width="full">
    <x-ui.table
        :empty="$logs->isEmpty()"
        empty-title="No audit logs yet"
        empty-description="Admin actions will be recorded here."
        empty-icon="audit"
        striped
    >
        <x-slot:filters>
            <form method="GET" class="flex flex-col sm:flex-row gap-3 max-w-md">
                <div class="flex-1">
                    <x-ui.input name="action" type="text" :value="request('action')" placeholder="Filter by action..." />
                </div>
                <x-ui.button type="submit" variant="secondary" size="md">Filter</x-ui.button>
            </form>
        </x-slot:filters>

        <x-slot:head>
            <x-ui.th>Time</x-ui.th>
            <x-ui.th>Admin</x-ui.th>
            <x-ui.th>Action</x-ui.th>
            <x-ui.th>Model</x-ui.th>
            <x-ui.th>IP</x-ui.th>
        </x-slot:head>

        @foreach ($logs as $log)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="text-text-muted text-xs">{{ $log->created_at->format('M j, Y H:i') }}</x-ui.td>
                <x-ui.td>{{ $log->admin?->email ?? '—' }}</x-ui.td>
                <x-ui.td class="font-mono text-xs">{{ $log->action }}</x-ui.td>
                <x-ui.td class="text-xs text-text-muted">{{ $log->model_type ? class_basename($log->model_type).' #'.$log->model_id : '—' }}</x-ui.td>
                <x-ui.td class="text-xs text-text-muted">{{ $log->ip ?? '—' }}</x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$logs" />
    </x-slot:pagination>
</x-layout.page>
@endsection
