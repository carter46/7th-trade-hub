@extends('layouts.dashboard-user')

@section('title', 'Discover Services')

@section('content')
<x-layout.page
    title="Services"
    subtitle="Browse platform services, reorder past purchases, and pay from your wallet."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Discover', route('dashboard.discover.services')],
        ['Services', null],
    ]"
>
    <x-slot:actions>
        @if($wallet)
            <span class="text-sm text-text-muted mr-2">Wallet: <strong class="text-text-primary">₦{{ number_format((float) $wallet->balance, 0) }}</strong></span>
        @endif
        <x-dashboard.button :href="route('services')" variant="secondary" size="sm">Public services</x-dashboard.button>
    </x-slot:actions>

    <div class="space-y-8">
        <x-dashboard.card>
            <form method="GET" class="flex flex-wrap gap-3">
                <div class="min-w-[16rem] flex-1">
                    <x-dashboard.input name="q" :value="$q" placeholder="Search services..." />
                </div>
                <x-dashboard.button type="submit" icon="search">Search</x-dashboard.button>
            </form>
        </x-dashboard.card>

        @if($recentlyPurchased->isNotEmpty())
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Recently purchased · Quick reorder</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recentlyPurchased as $product)
                        <x-dashboard.card>
                            <div class="font-semibold">{{ $product->title }}</div>
                            <a href="{{ route('services.show', $product->slug) }}" class="text-sm text-primary mt-2 inline-block">View / reorder →</a>
                        </x-dashboard.card>
                    @endforeach
                </div>
            </section>
        @endif

        @if($recentlyViewed->isNotEmpty())
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">You've viewed</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($recentlyViewed as $product)
                        <a href="{{ route('services.show', $product->slug) }}" class="rounded-xl border border-border-default px-3 py-2 text-sm hover:bg-muted/50">{{ $product->title }}</a>
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Popular & suggested</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($popular as $product)
                    <x-dashboard.card>
                        <div class="font-semibold">{{ $product->title }}</div>
                        <div class="text-xs text-text-muted mt-1">{{ $product->productType?->name }}</div>
                        <a href="{{ route('services.show', $product->slug) }}" class="text-sm text-primary mt-2 inline-block">Open →</a>
                    </x-dashboard.card>
                @endforeach
            </div>
        </section>

        @if($searchResults)
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Search results</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($searchResults as $product)
                        <x-dashboard.card>
                            <div class="font-semibold">{{ $product->title }}</div>
                            <a href="{{ route('services.show', $product->slug) }}" class="text-sm text-primary mt-2 inline-block">Open →</a>
                        </x-dashboard.card>
                    @empty
                        <p class="text-text-muted">No services matched.</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    <x-dashboard.pagination :paginator="$searchResults" />
                </div>
            </section>
        @else
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Categories</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($groups as $group)
                        <x-dashboard.card>
                            <div class="font-semibold">{{ $group['label'] ?? $group['name'] ?? 'Category' }}</div>
                            @if(!empty($group['href']))
                                <a href="{{ $group['href'] }}" class="text-sm text-primary mt-2 inline-block">Browse →</a>
                            @endif
                        </x-dashboard.card>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layout.page>
@endsection
