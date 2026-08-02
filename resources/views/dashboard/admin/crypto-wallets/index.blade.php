@extends('layouts.dashboard-admin')

@section('title', 'Deposit Wallets')

@section('content')
<x-layout.page
    title="Crypto deposit wallets"
    subtitle="Smart pool: up to {{ $maxActive }} active addresses per coin+network, up to {{ $maxPerWallet }} open orders each."
    width="full"
    :breadcrumb="[['Admin', route('admin')], ['Deposit wallets', null]]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.crypto-wallets.create')" icon="plus" size="sm">Add wallet</x-dashboard.button>
        <x-dashboard.button :href="route('admin.crypto-wallets.treasury')" variant="secondary" size="sm">Treasury inventory</x-dashboard.button>
    </x-slot:actions>

    @if(!empty($capacityByPair))
        <div class="mb-4 flex flex-wrap gap-2 text-xs text-text-muted">
            @foreach ($capacityByPair as $cap)
                <span class="rounded-lg border border-border-default bg-elevated px-2.5 py-1">{{ $cap['label'] }}</span>
            @endforeach
        </div>
    @endif

    <x-dashboard.table :empty="$wallets->isEmpty()" empty-title="No wallets" empty-description="Add a BTC, TRC20, or other deposit address." empty-icon="wallet" striped>
        <x-slot:head>
            <x-dashboard.th>Coin / Network</x-dashboard.th>
            <x-dashboard.th>Address</x-dashboard.th>
            <x-dashboard.th>Conf</x-dashboard.th>
            <x-dashboard.th>Open</x-dashboard.th>
            <x-dashboard.th>Capacity</x-dashboard.th>
            <x-dashboard.th>Active</x-dashboard.th>
            <x-dashboard.th></x-dashboard.th>
        </x-slot:head>
        @foreach ($wallets as $w)
            @php
                $open = $w->openOrdersUsingAddress();
                $cap = $w->capacityLabel($maxPerWallet);
            @endphp
            <tr>
                <x-dashboard.td>{{ $w->coin }} · {{ $w->network }}</x-dashboard.td>
                <x-dashboard.td class="font-mono text-xs break-all max-w-[14rem]">{{ $w->address }}</x-dashboard.td>
                <x-dashboard.td>{{ $w->required_confirmations }}</x-dashboard.td>
                <x-dashboard.td>{{ $open }}/{{ $maxPerWallet }}</x-dashboard.td>
                <x-dashboard.td>
                    <span @class([
                        'text-xs font-medium',
                        'text-success' => $cap === 'Available',
                        'text-warning' => $cap === 'Full',
                        'text-text-muted' => $cap === 'Disabled',
                    ])>{{ $cap }}</span>
                </x-dashboard.td>
                <x-dashboard.td>{{ $w->is_active ? 'Yes' : 'No' }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.button :href="route('admin.crypto-wallets.edit', $w)" size="sm" variant="secondary">Edit</x-dashboard.button>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
</x-layout.page>
@endsection
