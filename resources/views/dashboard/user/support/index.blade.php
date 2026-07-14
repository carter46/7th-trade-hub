@extends('layouts.dashboard-user')

@section('title', 'Support')

@section('content')
<x-layout.page title="Support Tickets" subtitle="Get help with deposits, orders, and account issues." width="full">
    <x-slot:actions>
        <x-ui.button :href="route('dashboard.support.create')" icon="plus">New Ticket</x-ui.button>
    </x-slot:actions>

    <x-ui.table
        :empty="$tickets->isEmpty()"
        empty-title="No tickets yet"
        empty-description="Open a ticket and our team will respond as soon as possible."
        empty-icon="support"
        :empty-action="['href' => route('dashboard.support.create'), 'label' => 'New Ticket']"
        striped
    >
        <x-slot:head>
            <x-ui.th>Subject</x-ui.th>
            <x-ui.th>Category</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th></x-ui.th>
        </x-slot:head>
        @foreach ($tickets as $t)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-medium">{{ $t->subject }}</x-ui.td>
                <x-ui.td>{{ $t->category }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge :status="$t->status === 'open' ? 'pending' : 'completed'">{{ $t->status }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td>
                    <x-ui.button :href="route('dashboard.support.show', $t)" variant="link" size="xs">View</x-ui.button>
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$tickets" />
    </x-slot:pagination>
</x-layout.page>
@endsection
