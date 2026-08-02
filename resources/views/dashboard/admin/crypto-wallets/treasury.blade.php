@extends('layouts.dashboard-admin')

@section('title', 'Treasury Inventory')

@section('content')
<x-layout.page
    title="Treasury Inventory"
    subtitle="Estimated holdings are manual — not a live Trust Wallet balance."
    width="full"
    :breadcrumb="[['Admin', route('admin')], ['Deposit wallets', route('admin.crypto-wallets')], ['Treasury', null]]"
>
    <x-dashboard.table :empty="$wallets->isEmpty()" empty-title="No wallets" empty-icon="wallet" striped>
        <x-slot:head>
            <x-dashboard.th>Asset</x-dashboard.th>
            <x-dashboard.th>Address</x-dashboard.th>
            <x-dashboard.th>Estimated holdings</x-dashboard.th>
            <x-dashboard.th>Updated</x-dashboard.th>
            <x-dashboard.th>Open orders</x-dashboard.th>
            <x-dashboard.th>Owner / Purpose</x-dashboard.th>
        </x-slot:head>
        @foreach ($wallets as $w)
            <tr>
                <x-dashboard.td>{{ $w->coin }} / {{ $w->network }}</x-dashboard.td>
                <x-dashboard.td class="font-mono text-xs break-all">{{ $w->address }}</x-dashboard.td>
                <x-dashboard.td>{{ $w->estimated_holdings !== null ? $w->estimated_holdings : '—' }}</x-dashboard.td>
                <x-dashboard.td class="text-xs">{{ $w->estimated_holdings_at ?: '—' }}</x-dashboard.td>
                <x-dashboard.td>{{ $w->openOrdersUsingAddress() }}</x-dashboard.td>
                <x-dashboard.td class="text-xs">{{ trim(($w->owner ?: '').' / '.($w->purpose ?: ''), ' /') ?: '—' }}</x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
</x-layout.page>
@endsection
