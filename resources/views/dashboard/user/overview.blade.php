@extends('layouts.dashboard-user')

@section('title', 'Dashboard')

@section('content')
<x-layout.page
    title="Welcome back, {{ auth()->user()->name ?? 'User' }}"
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Overview', null],
    ]"
>
    @if($openCryptoSell ?? null)
        <div class="mb-6 rounded-xl border border-primary/30 bg-primary/5 px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-text-primary">Continue your crypto sell</p>
                    <p class="text-xs text-text-muted">
                        {{ $openCryptoSell->tracking_code }} · {{ $openCryptoSell->coin }} · {{ str_replace('_', ' ', $openCryptoSell->status) }}
                    </p>
                </div>
                <x-dashboard.button :href="route('dashboard.crypto-sell.show', $openCryptoSell)" size="sm">Continue tracking</x-dashboard.button>
            </div>
        </div>
    @endif

    <div class="space-y-4">
        <x-dashboard.stats-card
            label="Total Balance"
            :value="'₦' . number_format($balanceNgn ?? 0, 2)"
            :hint="'Locked: ₦' . number_format($lockedNgn ?? 0, 2)"
            icon="wallet"
            :href="route('dashboard.wallet')"
        />

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-dashboard.stats-card
                label="My Tools"
                :value="(string) ($myToolsCount ?? 0)"
                hint="Websites & domains"
                icon="listings"
                :href="route('dashboard.my-tools')"
            />
            <x-dashboard.stats-card
                label="Active Orders"
                :value="(string) ($activeOrdersCount ?? 0)"
                :hint="$ordersAwaitingLabel ?? 'All caught up'"
                icon="shopping-bag"
                :href="route('dashboard.service-orders')"
            />
            <x-dashboard.stats-card
                label="Escrow chats"
                :value="(string) ($messagesCount ?? 0)"
                hint="Check inbox"
                icon="chat"
                :href="route('dashboard.messages')"
            />
            <x-dashboard.stats-card
                label="My Listings"
                :value="(string) ($myListingsCount ?? 0)"
                hint="Marketplace"
                icon="storefront"
                :href="route('dashboard.listings')"
            />
        </div>
    </div>

    <section class="mt-8 space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-text-primary">Services</h2>
                <p class="mt-1 text-sm text-text-secondary">Browse platform services and buy from your wallet.</p>
            </div>
            <x-dashboard.button :href="route('dashboard.services')" variant="secondary" size="sm">View all services</x-dashboard.button>
        </div>

        @if(($featuredServices ?? collect())->isEmpty())
            <x-dashboard.empty
                icon="listings"
                title="No services available"
                description="Check back soon for new platform services."
                :action="['href' => route('dashboard.services'), 'label' => 'Browse services']"
            />
        @else
            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                @foreach($featuredServices as $product)
                    @include('dashboard.user.partials.service-product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </section>

    @if(($recentlyPurchasedTools ?? collect())->isNotEmpty())
        <section class="mt-8 space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-text-primary">Recently purchased</h2>
                <p class="mt-1 text-sm text-text-secondary">Jump back to services in My Tools.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                @foreach($recentlyPurchasedTools as $tool)
                    @include('dashboard.user.partials.recently-purchased-tool-card', ['tool' => $tool])
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-8 space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-text-primary">Marketplace picks</h2>
                <p class="mt-1 text-sm text-text-secondary">Listings from the community marketplace.</p>
            </div>
            <x-dashboard.button :href="route('dashboard.marketplace')" variant="secondary" size="sm">Browse marketplace</x-dashboard.button>
        </div>

        @if(($marketplacePicks ?? collect())->isEmpty())
            <x-dashboard.empty
                icon="storefront"
                title="No marketplace listings yet"
                description="Browse the marketplace to discover services and assets."
                :action="['href' => route('dashboard.marketplace'), 'label' => 'Browse marketplace']"
            />
        @else
            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                @foreach($marketplacePicks as $listing)
                    <x-dashboard.card class="flex h-full flex-col">
                        <a href="{{ route('dashboard.marketplace.show', $listing->slug) }}" class="font-semibold text-text-primary line-clamp-2 hover:text-primary">{{ $listing->title }}</a>
                        <div class="mt-1 text-xs text-text-muted">{{ $listing->marketplaceProduct?->name ?? 'Listing' }}</div>
                        @if(filled($listing->description))
                            <p class="mt-2 text-sm text-text-secondary line-clamp-2">{{ $listing->description }}</p>
                        @endif
                        <div class="mt-auto flex flex-col gap-2 pt-3">
                            <div class="text-primary font-bold">₦{{ number_format((float) $listing->price, 0) }}</div>
                            <x-dashboard.button :href="route('dashboard.marketplace.show', $listing->slug)" size="sm" class="w-full sm:w-auto">View listing</x-dashboard.button>
                        </div>
                    </x-dashboard.card>
                @endforeach
            </div>
        @endif
    </section>
</x-layout.page>
@endsection
