@extends('layouts.dashboard-admin')

@section('title', 'Support Tickets')

@section('content')
<x-layout.page title="Support Tickets" subtitle="Handle support requests and tickets." width="full">
    <x-ui.table
        :empty="$tickets->isEmpty()"
        empty-title="No support tickets yet"
        empty-description="User support requests will appear here."
        empty-icon="support"
        striped
    >
        <x-slot:head>
            <x-ui.th>ID</x-ui.th>
            <x-ui.th>User</x-ui.th>
            <x-ui.th>Category</x-ui.th>
            <x-ui.th>Subject</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Created</x-ui.th>
        </x-slot:head>

        @foreach ($tickets as $ticket)
            <tr class="hover:bg-muted/50">
                <x-ui.td>
                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-primary hover:underline font-medium">#{{ $ticket->id }}</a>
                </x-ui.td>
                <x-ui.td>{{ $ticket->user?->email ?? '—' }}</x-ui.td>
                <x-ui.td class="text-text-muted text-xs">{{ $ticket->category }}</x-ui.td>
                <x-ui.td>{{ $ticket->subject }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge :status="$ticket->status === 'open' ? 'pending' : 'completed'">
                        {{ $ticket->status }}
                    </x-ui.badge>
                </x-ui.td>
                <x-ui.td class="text-text-muted text-xs">{{ $ticket->created_at->format('M j, Y H:i') }}</x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$tickets" />
    </x-slot:pagination>
</x-layout.page>
@endsection
