@extends('layouts.dashboard-user')

@section('title', 'Marketplace')

@section('content')
<x-layout.page
    title="Marketplace"
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Marketplace', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('dashboard.listings.create')" size="sm" icon="plus">Sell</x-dashboard.button>
    </x-slot:actions>

    <div class="space-y-6">
        <x-dashboard.card>
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
            <x-dashboard.empty icon="storefront" title="No listings found" description="Try another filter or create a listing to sell." />
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($listings as $listing)
                    <x-dashboard.card>
                        <a href="{{ route('dashboard.marketplace.show', $listing->slug) }}" class="font-semibold text-text-primary hover:text-primary">{{ $listing->title }}</a>
                        <div class="text-xs text-text-muted mt-1">{{ $listing->marketplaceProduct?->name ?? '—' }}</div>
                        <div class="text-primary font-bold mt-2">₦{{ number_format((float) $listing->price, 0) }}</div>
                        <x-dashboard.button class="mt-3" :href="route('dashboard.marketplace.checkout', $listing->slug)" size="sm">Buy</x-dashboard.button>
                    </x-dashboard.card>
                @endforeach
            </div>
        @endif
    </div>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$listings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
