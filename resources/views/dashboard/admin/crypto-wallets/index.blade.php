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

    <x-dashboard.table :empty="$wallets->isEmpty()" empty-title="No wallets" empty-description="Add a deposit address for a coin from your Coin Catalog." empty-icon="wallet" striped>
        <x-slot:head>
            <x-dashboard.th>Coin / Network</x-dashboard.th>
            <x-dashboard.th>Address</x-dashboard.th>
            <x-dashboard.th>Balance</x-dashboard.th>
            <x-dashboard.th>Open</x-dashboard.th>
            <x-dashboard.th>Capacity</x-dashboard.th>
            <x-dashboard.th>Active</x-dashboard.th>
            <x-dashboard.th></x-dashboard.th>
        </x-slot:head>
        @foreach ($wallets as $w)
            @php
                $open = $w->openOrdersUsingAddress();
                $cap = $w->capacityLabel($maxPerWallet);
                $logo = $logos[strtoupper($w->coin)] ?? null;
                $precision = (int) (config('crypto.amount_precision.'.strtoupper($w->coin)) ?? 8);
            @endphp
            <tr>
                <x-dashboard.td>
                    <div class="flex items-center gap-2">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="" class="h-6 w-6 rounded-full bg-white" width="24" height="24" loading="lazy" referrerpolicy="no-referrer">
                        @endif
                        <span>{{ $w->coin }} · {{ $w->network }}</span>
                    </div>
                </x-dashboard.td>
                <x-dashboard.td class="font-mono text-xs break-all max-w-[14rem]">{{ $w->address }}</x-dashboard.td>
                <x-dashboard.td class="text-xs">
                    @if ($w->live_balance !== null)
                        <div>{{ rtrim(rtrim(number_format((float) $w->live_balance, $precision, '.', ''), '0'), '.') ?: '0' }}</div>
                        <div class="text-text-muted">{{ $w->live_balance_updated_at?->diffForHumans() ?? '—' }}</div>
                    @else
                        <span class="text-text-muted">—</span>
                    @endif
                </x-dashboard.td>
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
