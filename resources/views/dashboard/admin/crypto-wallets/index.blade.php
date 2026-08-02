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
                $networkId = isset($networkRegistry) ? $networkRegistry->resolveId((string) $w->network) : strtolower((string) $w->network);
                $networkLabel = isset($networkRegistry) ? $networkRegistry->label($networkId) : $w->network;
                $supports = $supportsByNetwork[$networkId] ?? [];
            @endphp
            <tr>
                <x-dashboard.td>
                    <div class="flex items-center gap-2">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="" class="h-6 w-6 rounded-full bg-white" width="24" height="24" loading="lazy" referrerpolicy="no-referrer">
                        @endif
                        <div>
                            <div>{{ $w->coin }} · {{ $networkLabel }}</div>
                            @if ($supports !== [])
                                <div class="text-[11px] text-text-muted">Supports: {{ implode(', ', $supports) }}</div>
                            @endif
                        </div>
                    </div>
                </x-dashboard.td>
                <x-dashboard.td class="font-mono text-xs break-all max-w-[14rem]">{{ $w->address }}</x-dashboard.td>
                <x-dashboard.td class="text-xs">
                    @if ($w->live_balance !== null)
                        @php
                            $bal = (float) $w->live_balance;
                            $val = $valuations[strtoupper($w->coin)] ?? ['usd_price' => 0, 'ngn_per_usd' => 0];
                            $usd = $bal * (float) ($val['usd_price'] ?? 0);
                            $ngn = $usd * (float) ($val['ngn_per_usd'] ?? 0);
                        @endphp
                        <div>{{ rtrim(rtrim(number_format($bal, $precision, '.', ''), '0'), '.') ?: '0' }} {{ strtoupper($w->coin) }}</div>
                        @if ($usd > 0)
                            <div class="text-text-muted">${{ number_format($usd, 2) }} · ₦{{ number_format($ngn, 0) }}</div>
                        @endif
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
