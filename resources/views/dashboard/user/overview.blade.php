@extends('layouts.dashboard-user')

@section('title', 'Dashboard')

@section('content')
<x-layout.page title="Welcome back, {{ auth()->user()->name ?? 'User' }}" subtitle="Here's what's happening with your account today." width="content">
    <x-ui.stat-grid>
        <x-ui.stat-card
            label="Total Balance"
            :value="'₦' . number_format($balanceNgn ?? 0, 2)"
            :hint="'Locked: ₦' . number_format($lockedNgn ?? 0, 2)"
            icon="wallet"
        />
        <x-ui.stat-card
            label="My Listings"
            :value="(string) ($myListingsCount ?? 0)"
            hint="Create listing"
            icon="storefront"
            :href="route('dashboard.listings.create')"
        />
        <x-ui.stat-card
            label="Active Orders"
            :value="(string) ($activeOrdersCount ?? 0)"
            :hint="$ordersAwaitingLabel ?? 'All caught up'"
            icon="shopping-bag"
        />
        <x-ui.stat-card
            label="New Messages"
            :value="(string) ($messagesCount ?? 0)"
            hint="Check inbox"
            icon="chat"
            :href="route('dashboard.messages')"
        />
    </x-ui.stat-grid>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-ui.card class="lg:col-span-2">
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
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-text-primary">Recommended</h2>
                <x-ui.button :href="route('marketplace')" variant="link" size="sm">View All</x-ui.button>
            </div>
            <div class="space-y-3">
                @forelse($recommendedListings ?? collect() as $listing)
                    <a href="{{ route('marketplace.show', $listing->slug) }}" class="block p-4 rounded-xl bg-muted/40 hover:bg-muted/60 transition-colors border border-transparent hover:border-border-default">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <x-ui.icon name="listings" class="w-5 h-5" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-text-primary">{{ $listing->title }}</h3>
                                <p class="text-xs text-text-secondary mt-1 line-clamp-1">{{ $listing->description ?? '' }}</p>
                                <div class="mt-2 text-primary font-bold text-sm">₦{{ number_format($listing->price, 2) }}</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <x-ui.empty
                        icon="storefront"
                        title="No recommendations yet"
                        description="Browse the marketplace to discover services and assets."
                        :action="['href' => route('marketplace'), 'label' => 'Browse marketplace']"
                    />
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <x-ui.table
        :empty="($transactions ?? collect())->isEmpty()"
        empty-title="No transactions yet"
        empty-description="Your recent wallet activity will show up here."
        empty-icon="transactions"
        striped
    >
        <x-slot:head>
            <x-ui.th>Transaction ID</x-ui.th>
            <x-ui.th>Asset / Service</x-ui.th>
            <x-ui.th>Date</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
        </x-slot:head>
        @foreach($transactions ?? [] as $tx)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-mono text-xs">#{{ $tx->reference }}</x-ui.td>
                <x-ui.td>
                    <div class="flex items-center gap-3">
                        <span class="text-primary"><x-ui.icon name="paid" class="w-4 h-4" /></span>
                        <span>{{ $tx->label }}</span>
                    </div>
                </x-ui.td>
                <x-ui.td class="text-text-secondary text-xs">{{ $tx->created_at->format('M j, Y, H:i') }}</x-ui.td>
                <x-ui.td class="font-semibold">₦{{ number_format(abs($tx->amount), 2) }}</x-ui.td>
                <x-ui.td><x-ui.badge :status="$tx->status" /></x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layout.page>
@endsection
