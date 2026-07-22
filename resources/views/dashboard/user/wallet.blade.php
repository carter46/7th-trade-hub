@extends('layouts.dashboard-user')

@section('title', 'Wallet')

@section('content')
<x-layout.page title="Wallet" subtitle="Your NGN platform wallet." width="full">
    @if (! $wallet)
        <x-dashboard.card>
            @if (($kycLevel ?? 0) < 1)
                <x-dashboard.alert type="warning" title="KYC required">
                    Complete <a href="{{ route('dashboard.kyc') }}" class="underline font-medium">KYC Level 1</a> then create your wallet.
                </x-dashboard.alert>
            @else
                <form method="POST" action="{{ route('dashboard.wallet.create') }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <x-dashboard.button type="submit" icon="wallet" x-bind:disabled="submitting">Create Wallet</x-dashboard.button>
                </form>
            @endif
        </x-dashboard.card>
    @else
        <x-dashboard.stat-grid :count="3">
            <x-dashboard.stats-card
                label="Available (NGN)"
                :value="'₦' . number_format($wallet->balance, 2)"
                icon="wallet"
            />
            <x-dashboard.stats-card
                label="Locked (Escrow / Withdrawal)"
                :value="'₦' . number_format($wallet->locked_balance, 2)"
                icon="lock"
            />
            <x-dashboard.card class="flex flex-col justify-center gap-3 min-h-[120px]">
                <x-dashboard.button :href="route('dashboard.deposit.index')" variant="secondary" size="sm" icon="deposit">Deposit Money</x-dashboard.button>
                <x-dashboard.button :href="route('dashboard.crypto-sell.index')" variant="secondary" size="sm" icon="bitcoin">Sell Crypto</x-dashboard.button>
                <x-dashboard.button :href="route('dashboard.withdrawal.create')" variant="secondary" size="sm" icon="withdraw">Withdraw to Bank</x-dashboard.button>
                <x-dashboard.button :href="route('dashboard.history')" variant="ghost" size="sm" icon="history">Transaction History</x-dashboard.button>
            </x-dashboard.card>
        </x-dashboard.stat-grid>

        <x-dashboard.table
            :empty="$transactions->isEmpty()"
            empty-title="No transactions yet"
            empty-description="Deposits, withdrawals, and marketplace activity will appear here."
            empty-icon="transactions"
            striped
        >
            <x-slot:head>
                <x-dashboard.th>Reference</x-dashboard.th>
                <x-dashboard.th>Type</x-dashboard.th>
                <x-dashboard.th>Amount (NGN)</x-dashboard.th>
                <x-dashboard.th>Status</x-dashboard.th>
            </x-slot:head>
            @foreach ($transactions as $tx)
                <tr class="hover:bg-muted/50">
                    <x-dashboard.td class="font-medium">{{ $tx->reference }}</x-dashboard.td>
                    <x-dashboard.td>{{ $tx->type }}</x-dashboard.td>
                    <x-dashboard.td>₦{{ number_format($tx->amount, 2) }}</x-dashboard.td>
                    <x-dashboard.td><x-dashboard.badge :status="$tx->status" /></x-dashboard.td>
                </tr>
            @endforeach
        </x-dashboard.table>
    @endif
</x-layout.page>
@endsection
