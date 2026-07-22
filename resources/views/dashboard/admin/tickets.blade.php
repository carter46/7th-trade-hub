@extends('layouts.dashboard-admin')

@section('title', 'Support Tickets')

@section('content')
<x-layout.page
    title="Support Tickets"
    subtitle="Handle support requests and tickets."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Support Tickets', null],
    ]"
>
    <x-dashboard.table
        :empty="$tickets->isEmpty()"
        empty-title="No support tickets yet"
        empty-description="User support requests will appear here."
        empty-icon="support"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>ID</x-dashboard.th>
            <x-dashboard.th>User</x-dashboard.th>
            <x-dashboard.th>Category</x-dashboard.th>
            <x-dashboard.th>Subject</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Created</x-dashboard.th>
        </x-slot:head>

        @foreach ($tickets as $ticket)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td>
                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-primary hover:underline font-medium">#{{ $ticket->id }}</a>
                </x-dashboard.td>
                <x-dashboard.td>{{ $ticket->user?->email ?? '—' }}</x-dashboard.td>
                <x-dashboard.td class="text-text-muted text-xs">{{ $ticket->category }}</x-dashboard.td>
                <x-dashboard.td>{{ $ticket->subject }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$ticket->status === 'open' ? 'pending' : 'completed'">
                        {{ $ticket->status }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td class="text-text-muted text-xs">{{ $ticket->created_at->format('M j, Y H:i') }}</x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$tickets" />
    </x-slot:pagination>
</x-layout.page>
@endsection
