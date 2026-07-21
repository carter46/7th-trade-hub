@extends('layouts.dashboard-user')

@section('title', 'Dashboard')

@section('content')
<x-layout.page title="Welcome back, {{ auth()->user()->name ?? 'User' }}" subtitle="Here's what's happening with your account today." width="content">
    <x-dashboard.stat-grid>
        <x-dashboard.stats-card
            label="Total Balance"
            :value="'₦' . number_format($balanceNgn ?? 0, 2)"
            :hint="'Locked: ₦' . number_format($lockedNgn ?? 0, 2)"
            icon="wallet"
        />
        <x-dashboard.stats-card
            label="My Listings"
            :value="(string) ($myListingsCount ?? 0)"
            hint="Create listing"
            icon="storefront"
            :href="route('dashboard.listings.create')"
        />
        <x-dashboard.stats-card
            label="Active Orders"
            :value="(string) ($activeOrdersCount ?? 0)"
            :hint="$ordersAwaitingLabel ?? 'All caught up'"
            icon="shopping-bag"
        />
        <x-dashboard.stats-card
            label="New Messages"
            :value="(string) ($messagesCount ?? 0)"
            hint="Check inbox"
            icon="chat"
            :href="route('dashboard.messages')"
        />
    </x-dashboard.stat-grid>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-dashboard.card class="lg:col-span-2">
            <h2 class="text-lg font-semibold text-text-primary">Wallet Activity</h2>
            <p class="text-sm text-text-secondary mt-1">Summary from your ledger</p>
            <div class="grid grid-cols-2 gap-4 mt-6">
                <div class="rounded-xl bg-muted/40 p-5">
                    <p class="text-sm text-text-secondary">Available balance</p>
                    <p class="text-2xl font-bold text-text-primary mt-2">₦{{ number_format($balanceNgn ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl bg-muted/40 p-5">
                    <p class="text-sm text-text-secondary">In escrow</p>
                    <p class="text-2xl font-bold text-text-primary mt-2">₦{{ number_format($lockedNgn ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl bg-muted/40 p-5 col-span-2">
                    <p class="text-sm text-text-secondary">Recent transactions</p>
                    <p class="text-2xl font-bold text-text-primary mt-2">{{ ($transactions ?? collect())->count() }}</p>
                </div>
            </div>
        </x-dashboard.card>

        <x-dashboard.card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-text-primary">Recommended</h2>
                <x-dashboard.button :href="route('marketplace')" variant="link" size="sm">View All</x-dashboard.button>
            </div>
            <div class="space-y-3">
                @forelse($recommendedListings ?? collect() as $listing)
                    <a href="{{ route('marketplace.show', $listing->slug) }}" class="block p-4 rounded-xl bg-muted/40 hover:bg-muted/60 transition-colors border border-transparent hover:border-border-default">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <x-dashboard.icon name="listings" class="w-5 h-5" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-text-primary">{{ $listing->title }}</h3>
                                <p class="text-xs text-text-secondary mt-1 line-clamp-1">{{ $listing->description ?? '' }}</p>
                                <div class="mt-2 text-primary font-bold text-sm">₦{{ number_format($listing->price, 2) }}</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <x-dashboard.empty
                        icon="storefront"
                        title="No recommendations yet"
                        description="Browse the marketplace to discover services and assets."
                        :action="['href' => route('marketplace'), 'label' => 'Browse marketplace']"
                    />
                @endforelse
            </div>
        </x-dashboard.card>
    </div>

    <x-dashboard.table
        :empty="($transactions ?? collect())->isEmpty()"
        empty-title="No transactions yet"
        empty-description="Your recent wallet activity will show up here."
        empty-icon="transactions"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Transaction ID</x-dashboard.th>
            <x-dashboard.th>Asset / Service</x-dashboard.th>
            <x-dashboard.th>Date</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
        </x-slot:head>
        @foreach($transactions ?? [] as $tx)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-mono text-xs">#{{ $tx->reference }}</x-dashboard.td>
                <x-dashboard.td>
                    <div class="flex items-center gap-3">
                        <span class="text-primary"><x-dashboard.icon name="paid" class="w-4 h-4" /></span>
                        <span>{{ $tx->label }}</span>
                    </div>
                </x-dashboard.td>
                <x-dashboard.td class="text-text-secondary text-xs">{{ $tx->created_at->format('M j, Y, H:i') }}</x-dashboard.td>
                <x-dashboard.td class="font-semibold">₦{{ number_format(abs($tx->amount), 2) }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$tx->status" /></x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
</x-layout.page>
@endsection
