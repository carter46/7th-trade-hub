@extends('layouts.dashboard-user')

@section('title', 'Escrow Conversations')

@section('content')
@php
    $filter = $filter ?? 'active';
@endphp
<x-layout.page
    title="Escrow Conversations"
    subtitle="Chat with the other party on marketplace escrow orders. Replies are only open while escrow is active."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Marketplace', route('dashboard.marketplace')],
        ['Escrow Conversations', null],
    ]"
>
    <x-slot:actions>
        <div class="flex flex-wrap gap-2">
            @foreach (['active' => 'Active', 'closed' => 'Closed', 'all' => 'All'] as $key => $label)
                <a
                    href="{{ route('dashboard.messages', ['status' => $key]) }}"
                    @class([
                        'inline-flex min-h-10 items-center rounded-xl border px-3 py-1.5 text-sm font-semibold',
                        $filter === $key
                            ? 'border-primary bg-primary/10 text-primary'
                            : 'border-border-default bg-elevated text-text-secondary hover:text-text-primary',
                    ])
                >{{ $label }}</a>
            @endforeach
        </div>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$threads->isEmpty()"
        empty-title="No escrow chats here"
        empty-description="A conversation appears when you buy or sell a listing and funds are held in escrow."
        empty-icon="messages"
        :empty-action="['href' => route('dashboard.marketplace'), 'label' => 'Browse marketplace']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Order</x-dashboard.th>
            <x-dashboard.th>Listing</x-dashboard.th>
            <x-dashboard.th>With</x-dashboard.th>
            <x-dashboard.th>Escrow</x-dashboard.th>
            <x-dashboard.th></x-dashboard.th>
        </x-slot:head>
        @foreach ($threads as $order)
            @php
                $isBuyer = (int) $order->user_id === (int) auth()->id();
                $counterpart = $isBuyer ? $order->listing?->user : $order->user;
            @endphp
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-mono text-sm">{{ $order->reference }}</x-dashboard.td>
                <x-dashboard.td>{{ $order->listing?->title ?? 'Listing unavailable' }}</x-dashboard.td>
                <x-dashboard.td>{{ $counterpart?->name ?? '—' }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$order->escrow?->status ?? 'default'">{{ $order->escrow?->status ?? '—' }}</x-dashboard.badge></x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.button :href="route('dashboard.messages.order', $order)" size="xs">Open</x-dashboard.button>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$threads" />
    </x-slot:pagination>
</x-layout.page>
@endsection
