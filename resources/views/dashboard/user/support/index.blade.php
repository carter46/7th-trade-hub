@extends('layouts.dashboard-user')

@section('title', 'Support')

@section('content')
<x-layout.page title="Support Tickets" subtitle="Get help with deposits, orders, and account issues." width="full">
    <x-slot:actions>
        <x-dashboard.button :href="route('dashboard.support.create')" icon="plus">New Ticket</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$tickets->isEmpty()"
        empty-title="No tickets yet"
        empty-description="Open a ticket and our team will respond as soon as possible."
        empty-icon="support"
        :empty-action="['href' => route('dashboard.support.create'), 'label' => 'New Ticket']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Subject</x-dashboard.th>
            <x-dashboard.th>Category</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th></x-dashboard.th>
        </x-slot:head>
        @foreach ($tickets as $t)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-medium">{{ $t->subject }}</x-dashboard.td>
                <x-dashboard.td>{{ $t->category }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$t->status === 'open' ? 'pending' : 'completed'">{{ $t->status }}</x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.button :href="route('dashboard.support.show', $t)" variant="link" size="xs">View</x-dashboard.button>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$tickets" />
    </x-slot:pagination>
</x-layout.page>
@endsection
