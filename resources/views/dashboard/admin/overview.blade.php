@extends('layouts.dashboard-admin')

@section('title', 'Overview')

@section('content')
@php
    $txs = collect($recentTransactions ?? []);
@endphp
<x-layout.page title="Dashboard Overview" subtitle="Welcome back. Here's what's happening with your platform today." width="full">
    <x-dashboard.stat-grid>
        <x-dashboard.stats-card
            label="Total Users"
            :value="number_format($userCount ?? 0)"
            icon="group"
        />
        <x-dashboard.stats-card
            label="Total Sales"
            :value="'₦' . number_format($totalSales ?? 0, 2)"
            icon="paid"
        />
        <x-dashboard.stats-card
            label="Pending Crypto"
            :value="number_format($pendingCrypto ?? 0)"
            icon="bitcoin"
        />
        <x-dashboard.stats-card
            label="Support Tickets"
            :value="number_format($ticketCount ?? 0)"
            icon="support"
        />
    </x-dashboard.stat-grid>

    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-text-primary">Recent Crypto Transactions</h2>
            <x-dashboard.button :href="route('admin.transactions')" variant="link" size="sm">View All</x-dashboard.button>
        </div>

        <x-dashboard.table
            :empty="$txs->isEmpty()"
            empty-title="No transactions yet"
            empty-description="Run php artisan db:seed to load demo data."
            empty-icon="transactions"
            striped
            :min-height="false"
        >
            <x-slot:head>
                <x-dashboard.th>Transaction ID</x-dashboard.th>
                <x-dashboard.th>User</x-dashboard.th>
                <x-dashboard.th>Asset</x-dashboard.th>
                <x-dashboard.th>Amount</x-dashboard.th>
                <x-dashboard.th>Status</x-dashboard.th>
                <x-dashboard.th>Date</x-dashboard.th>
            </x-slot:head>

            @foreach ($txs as $tx)
                <tr class="hover:bg-muted/50">
                    <x-dashboard.td class="font-mono text-xs">#{{ $tx->reference }}</x-dashboard.td>
                    <x-dashboard.td class="font-medium">{{ $tx->user?->name ?? '—' }}</x-dashboard.td>
                    <x-dashboard.td>
                        <div class="flex items-center gap-1.5">
                            <x-dashboard.icon name="bitcoin" class="w-4 h-4 {{ ($tx->asset_type ?? '') === 'BTC' ? 'text-warning' : 'text-text-muted' }}" />
                            <span>{{ $tx->asset_type ?? $tx->currency ?? 'USD' }}</span>
                        </div>
                    </x-dashboard.td>
                    <x-dashboard.td class="font-medium">
                        {{ $tx->currency !== 'USD' ? number_format($tx->amount, 3) . ' ' . ($tx->asset_type ?? $tx->currency) : '$' . number_format($tx->amount, 2) }}
                    </x-dashboard.td>
                    <x-dashboard.td><x-dashboard.badge :status="$tx->status" /></x-dashboard.td>
                    <x-dashboard.td class="text-text-muted text-xs">{{ $tx->created_at->format('M j, Y') }}</x-dashboard.td>
                </tr>
            @endforeach
        </x-dashboard.table>
    </div>
</x-layout.page>
@endsection
