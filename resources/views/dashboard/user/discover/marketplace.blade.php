@extends('layouts.dashboard-user')

@section('title', 'Discover Marketplace')

@section('content')
<x-layout.page
    title="Marketplace"
    subtitle="Browse listings, continue where you left off, and buy with your wallet."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Discover', route('dashboard.discover.marketplace')],
        ['Marketplace', null],
    ]"
>
    <x-slot:actions>
        @if($wallet)
            <span class="text-sm text-text-muted mr-2">Wallet: <strong class="text-text-primary">₦{{ number_format((float) $wallet->balance, 0) }}</strong></span>
        @endif
        <x-dashboard.button :href="route('dashboard.listings.create')" size="sm" icon="plus">Sell</x-dashboard.button>
        <x-dashboard.button :href="route('dashboard.watchlist')" variant="secondary" size="sm">Saved</x-dashboard.button>
    </x-slot:actions>

    <div class="space-y-8">
        @if($continueBrowsing->isNotEmpty())
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Continue browsing</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($continueBrowsing as $listing)
                        <x-dashboard.card>
                            <a href="{{ route('dashboard.discover.marketplace.show', $listing->slug) }}" class="font-semibold text-text-primary hover:text-primary">{{ $listing->title }}</a>
                            <div class="text-sm text-text-muted mt-1">{{ $listing->marketplaceProduct?->name }}</div>
                            <div class="text-primary font-bold mt-2">₦{{ number_format((float) $listing->price, 0) }}</div>
                        </x-dashboard.card>
                    @endforeach
                </div>
            </section>
        @endif

        @if($recommended->isNotEmpty())
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Recommended</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recommended as $listing)
                        <x-dashboard.card>
                            <a href="{{ route('dashboard.discover.marketplace.show', $listing->slug) }}" class="font-semibold text-text-primary hover:text-primary">{{ $listing->title }}</a>
                            <div class="text-primary font-bold mt-2">₦{{ number_format((float) $listing->price, 0) }}</div>
                            <x-dashboard.button class="mt-3" :href="route('marketplace.checkout', $listing->slug)" size="sm">Quick buy</x-dashboard.button>
                        </x-dashboard.card>
                    @endforeach
                </div>
            </section>
        @endif

        @if($saved->isNotEmpty())
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Saved items</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($saved as $listing)
                        <a href="{{ route('dashboard.discover.marketplace.show', $listing->slug) }}" class="rounded-xl border border-border-default px-3 py-2 text-sm hover:bg-muted/50">{{ $listing->title }}</a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($recentlyPurchased->isNotEmpty())
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Recently purchased</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($recentlyPurchased as $listing)
                        <a href="{{ route('dashboard.discover.marketplace.show', $listing->slug) }}" class="rounded-xl border border-border-default px-3 py-2 text-sm hover:bg-muted/50">{{ $listing->title }}</a>
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide">All listings</h2>
                <a href="{{ route('marketplace') }}" class="text-sm text-primary">Public marketplace →</a>
            </div>
            <x-dashboard.card class="mb-4">
                <form method="GET" class="grid md:grid-cols-5 gap-3">
                    <x-dashboard.input name="q" :value="$filters['q']" placeholder="Search listings..." />
                    <select name="category" class="rounded-xl border-border-default bg-elevated">
                        <option value="">All categories</option>
                        @foreach($parents as $cat)
                            <option value="{{ $cat->id }}" @selected($filters['category'] == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <select name="product" class="rounded-xl border-border-default bg-elevated">
                        <option value="">All products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(($filters['product'] ?? null) == $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                    <select name="sort" class="rounded-xl border-border-default bg-elevated">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest</option>
                        <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Price ↑</option>
                        <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Price ↓</option>
                    </select>
                    <x-dashboard.button type="submit" icon="search">Filter</x-dashboard.button>
                </form>
            </x-dashboard.card>

            @if($listings->isEmpty())
                <x-dashboard.empty icon="storefront" title="No listings found" description="Try another filter or browse the public marketplace." />
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($listings as $listing)
                        <x-dashboard.card>
                            <a href="{{ route('dashboard.discover.marketplace.show', $listing->slug) }}" class="font-semibold text-text-primary hover:text-primary">{{ $listing->title }}</a>
                            <div class="text-xs text-text-muted mt-1">{{ $listing->marketplaceProduct?->name ?? '—' }}</div>
                            <div class="text-primary font-bold mt-2">₦{{ number_format((float) $listing->price, 0) }}</div>
                        </x-dashboard.card>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$listings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
