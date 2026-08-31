@extends('layouts.dashboard-user')

@section('title', 'Services')

@section('content')
<x-layout.page
    title="Services"
    subtitle="Browse platform services and pay from your wallet."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Services', null],
    ]"
>
    <x-slot:actions>
        @if($wallet)
            <span class="text-sm text-text-muted mr-2">Wallet: <strong class="text-text-primary">₦{{ number_format((float) $wallet->balance, 0) }}</strong></span>
        @endif
        <x-dashboard.button :href="route('dashboard.service-orders')" variant="secondary" size="sm" icon="orders">My Orders</x-dashboard.button>
    </x-slot:actions>

    <div class="space-y-8">
        <x-dashboard.card>
            <form method="GET" class="flex flex-wrap gap-3">
                <div class="min-w-[16rem] flex-1">
                    <x-dashboard.input name="q" :value="$q" placeholder="Search services..." />
                </div>
                <x-dashboard.button type="submit" size="sm" icon="search">Search</x-dashboard.button>
            </form>
        </x-dashboard.card>

        @if($recentlyPurchased->isNotEmpty())
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Recently purchased · Quick reorder</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recentlyPurchased as $product)
                        <x-dashboard.card>
                            <div class="font-semibold">{{ $product->title }}</div>
                            @if(filled($product->short_description))
                                <p class="mt-1 text-sm text-text-secondary line-clamp-2">{{ $product->short_description }}</p>
                            @endif
                            <div class="mt-3">
                                <x-dashboard.button :href="route('dashboard.services.product', $product->slug)" size="xs">View / reorder</x-dashboard.button>
                            </div>
                        </x-dashboard.card>
                    @endforeach
                </div>
            </section>
        @endif

        @if($searchResults)
            <section>
                <h2 class="text-sm font-semibold text-text-secondary uppercase tracking-wide mb-3">Search results</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($searchResults as $product)
                        <x-dashboard.card>
                            <div class="font-semibold">{{ $product->title }}</div>
                            @if(filled($product->short_description))
                                <p class="mt-1 text-sm text-text-secondary line-clamp-2">{{ $product->short_description }}</p>
                            @endif
                            <div class="mt-3">
                                <x-dashboard.button :href="route('dashboard.services.product', $product->slug)" size="xs">Open</x-dashboard.button>
                            </div>
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
                        @php
                            $image = $group['card_image'] ?? null;
                            $imageSrc = null;
                            if (is_string($image) && $image !== '') {
                                $trimmed = trim($image);
                                if (preg_match('#^(https?:)?//#i', $trimmed) || str_starts_with($trimmed, '/')) {
                                    $imageSrc = $trimmed;
                                } else {
                                    $imageSrc = asset(ltrim(str_replace('\\', '/', $trimmed), '/'));
                                }
                            }
                            $label = $group['label'] ?? $group['name'] ?? 'Category';
                            $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $label) ?: 'S', 0, 2));
                        @endphp
                        <x-dashboard.card :padding="false" class="overflow-hidden">
                            <div class="relative aspect-[2/1] overflow-hidden bg-muted">
                                @if($imageSrc)
                                    <img src="{{ $imageSrc }}" alt="" class="h-full w-full object-cover">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/40 via-muted to-elevated">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/15 text-xs font-bold text-white" aria-hidden="true">
                                            {{ $initials }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="space-y-2 p-4">
                                <div class="font-semibold">{{ $label }}</div>
                                @if(!empty($group['short_description']))
                                    <p class="text-sm text-text-secondary line-clamp-2">{{ $group['short_description'] }}</p>
                                @endif
                                @if(!empty($group['href']))
                                    <x-dashboard.button :href="$group['href']" size="xs">
                                        {{ $group['cta'] ?? 'Browse' }}
                                    </x-dashboard.button>
                                @endif
                            </div>
                        </x-dashboard.card>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layout.page>
@endsection
