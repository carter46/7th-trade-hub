@extends('layouts.dashboard-user')

@section('title', 'Wallet')

@section('content')
<x-layout.page title="Wallet" subtitle="Your NGN platform wallet." width="content">
    @if (! $wallet)
        <x-ui.card>
            @if (($kycLevel ?? 0) < 1)
                <x-ui.alert type="warning" title="KYC required">
                    Complete <a href="{{ route('dashboard.kyc') }}" class="underline font-medium">KYC Level 1</a> then create your wallet.
                </x-ui.alert>
            @else
                <form method="POST" action="{{ route('dashboard.wallet.create') }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <x-ui.button type="submit" icon="wallet" x-bind:disabled="submitting">Create Wallet</x-ui.button>
                </form>
            @endif
        </x-ui.card>
    @else
        <x-ui.stat-grid :count="3">
            <x-ui.stat-card
                label="Available (NGN)"
                :value="'₦' . number_format($wallet->balance, 2)"
                icon="wallet"
            />
            <x-ui.stat-card
                label="Locked (Escrow / Withdrawal)"
                :value="'₦' . number_format($wallet->locked_balance, 2)"
                icon="lock"
            />
            <x-ui.card class="flex flex-col justify-center gap-3 min-h-[120px]">
                <x-ui.button :href="route('dashboard.deposit.index')" variant="secondary" size="sm" icon="deposit">Deposit Money</x-ui.button>
                <x-ui.button :href="route('dashboard.crypto-sell.index')" variant="secondary" size="sm" icon="bitcoin">Sell Crypto</x-ui.button>
                <x-ui.button :href="route('dashboard.withdrawal.create')" variant="secondary" size="sm" icon="withdraw">Withdraw to Bank</x-ui.button>
                <x-ui.button :href="route('dashboard.history')" variant="ghost" size="sm" icon="history">Transaction History</x-ui.button>
            </x-ui.card>
        </x-ui.stat-grid>

        <x-ui.table
            :empty="$transactions->isEmpty()"
            empty-title="No transactions yet"
            empty-description="Deposits, withdrawals, and marketplace activity will appear here."
            empty-icon="transactions"
            striped
        >
            <x-slot:head>
                <x-ui.th>Reference</x-ui.th>
                <x-ui.th>Type</x-ui.th>
                <x-ui.th>Amount (NGN)</x-ui.th>
                <x-ui.th>Status</x-ui.th>
            </x-slot:head>
            @foreach ($transactions as $tx)
                <tr class="hover:bg-muted/50">
                    <x-ui.td class="font-medium">{{ $tx->reference }}</x-ui.td>
                    <x-ui.td>{{ $tx->type }}</x-ui.td>
                    <x-ui.td>₦{{ number_format($tx->amount, 2) }}</x-ui.td>
                    <x-ui.td><x-ui.badge :status="$tx->status" /></x-ui.td>
                </tr>
            @endforeach
        </x-ui.table>
    @endif
</x-layout.page>
@endsection
