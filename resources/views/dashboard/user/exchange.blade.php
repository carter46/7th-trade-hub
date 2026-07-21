@extends('layouts.dashboard-user')

@section('title', 'Crypto Exchange')

@section('content')
<x-layout.page title="Crypto Exchange" subtitle="Sell crypto to fund your NGN wallet (OTC)." width="content">
    <x-dashboard.stat-grid :count="2">
        <x-dashboard.stats-card
            label="Available balance (NGN)"
            :value="'₦' . number_format($wallet->balance ?? 0, 2)"
            icon="wallet"
        />
        <x-dashboard.stats-card
            label="Locked (escrow / pending)"
            :value="'₦' . number_format($wallet->locked_balance ?? 0, 2)"
            icon="lock"
        />
    </x-dashboard.stat-grid>

    <x-dashboard.card>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <p class="text-text-secondary text-sm">
                Request an OTC crypto sell quote. After you send crypto and we verify, your wallet is credited in NGN.
            </p>
            <x-dashboard.button :href="route('dashboard.crypto-sell.create')" icon="bitcoin">Sell Crypto</x-dashboard.button>
        </div>
    </x-dashboard.card>
</x-layout.page>
@endsection
