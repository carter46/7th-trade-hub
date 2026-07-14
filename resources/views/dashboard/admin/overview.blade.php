@extends('layouts.dashboard-admin')

@section('title', 'Overview')

@section('content')
@php
    $txs = collect($recentTransactions ?? []);
@endphp
<x-layout.page title="Dashboard Overview" subtitle="Welcome back. Here's what's happening with your platform today." width="full">
    <x-ui.stat-grid>
        <x-ui.stat-card
            label="Total Users"
            :value="number_format($userCount ?? 0)"
            icon="group"
        />
        <x-ui.stat-card
            label="Total Sales"
            :value="'₦' . number_format($totalSales ?? 0, 2)"
            icon="paid"
        />
        <x-ui.stat-card
            label="Pending Crypto"
            :value="number_format($pendingCrypto ?? 0)"
            icon="bitcoin"
        />
        <x-ui.stat-card
            label="Support Tickets"
            :value="number_format($ticketCount ?? 0)"
            icon="support"
        />
    </x-ui.stat-grid>

    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-text-primary">Recent Crypto Transactions</h2>
            <x-ui.button :href="route('admin.transactions')" variant="link" size="sm">View All</x-ui.button>
        </div>

        <x-ui.table
            :empty="$txs->isEmpty()"
            empty-title="No transactions yet"
            empty-description="Run php artisan db:seed to load demo data."
            empty-icon="transactions"
            striped
            :min-height="false"
        >
            <x-slot:head>
                <x-ui.th>Transaction ID</x-ui.th>
                <x-ui.th>User</x-ui.th>
                <x-ui.th>Asset</x-ui.th>
                <x-ui.th>Amount</x-ui.th>
                <x-ui.th>Status</x-ui.th>
                <x-ui.th>Date</x-ui.th>
            </x-slot:head>

            @foreach ($txs as $tx)
                <tr class="hover:bg-muted/50">
                    <x-ui.td class="font-mono text-xs">#{{ $tx->reference }}</x-ui.td>
                    <x-ui.td class="font-medium">{{ $tx->user?->name ?? '—' }}</x-ui.td>
                    <x-ui.td>
                        <div class="flex items-center gap-1.5">
                            <x-ui.icon name="bitcoin" class="w-4 h-4 {{ ($tx->asset_type ?? '') === 'BTC' ? 'text-warning' : 'text-text-muted' }}" />
                            <span>{{ $tx->asset_type ?? $tx->currency ?? 'USD' }}</span>
                        </div>
                    </x-ui.td>
                    <x-ui.td class="font-medium">
                        {{ $tx->currency !== 'USD' ? number_format($tx->amount, 3) . ' ' . ($tx->asset_type ?? $tx->currency) : '$' . number_format($tx->amount, 2) }}
                    </x-ui.td>
                    <x-ui.td><x-ui.badge :status="$tx->status" /></x-ui.td>
                    <x-ui.td class="text-text-muted text-xs">{{ $tx->created_at->format('M j, Y') }}</x-ui.td>
                </tr>
            @endforeach
        </x-ui.table>
    </div>
</x-layout.page>
@endsection
