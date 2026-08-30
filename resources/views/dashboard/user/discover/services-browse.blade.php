@extends('layouts.dashboard-user')

@section('title', $title)

@section('content')
<x-layout.page
    :title="$title"
    :subtitle="$subtitle"
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Services', route('dashboard.services')],
        [$title, null],
    ]"
>
    <x-slot:actions>
        @if($wallet)
            <span class="text-sm text-text-muted">Wallet: <strong class="text-text-primary">₦{{ number_format((float) $wallet->balance, 0) }}</strong></span>
        @endif
    </x-slot:actions>

    <div class="space-y-6">
        @if(!empty($typeCards) && $typeCards->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($typeCards as $card)
                    @php
                        $image = $card['card_image'] ?? null;
                        $imageSrc = null;
                        if (is_string($image) && $image !== '') {
                            $trimmed = trim($image);
                            if (preg_match('#^(https?:)?//#i', $trimmed) || str_starts_with($trimmed, '/')) {
                                $imageSrc = $trimmed;
                            } else {
                                $imageSrc = asset(ltrim(str_replace('\\', '/', $trimmed), '/'));
                            }
                        }
                        $label = $card['label'] ?? $card['slug'] ?? 'Service';
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
                            <div class="font-semibold text-text-primary">{{ $label }}</div>
                            @if(!empty($card['short_description']))
                                <p class="text-sm text-text-secondary line-clamp-2">{{ $card['short_description'] }}</p>
                            @endif
                            @if(!empty($card['meta']))
                                <p class="text-xs text-text-muted">{{ $card['meta'] }}</p>
                            @endif
                            @if(!empty($card['href']))
                                <x-dashboard.button :href="$card['href']" size="xs">
                                    {{ $card['cta'] ?? 'View products' }}
                                </x-dashboard.button>
                            @endif
                        </div>
                    </x-dashboard.card>
                @endforeach
            </div>
        @else
            <x-dashboard.card>
                <form method="GET" action="{{ route('dashboard.services.browse', $segment) }}" class="flex flex-wrap gap-3">
                    @if(($filters['type'] ?? null))
                        <input type="hidden" name="type" value="{{ $filters['type'] }}">
                    @endif
                    <div class="min-w-[16rem] flex-1">
                        <x-dashboard.input name="q" :value="$filters['q'] ?? ''" placeholder="Search in {{ $title }}..." />
                    </div>
                    <x-dashboard.button type="submit" size="sm" icon="search">Search</x-dashboard.button>
                </form>
            </x-dashboard.card>

            @if(!$products || $products->isEmpty())
                <p class="text-text-muted">No products found.</p>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($products as $product)
                        @php
                            $heroUrl = media_url($product->heroMedia, $product->hero_image, 'medium');
                            $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $product->title) ?: 'P', 0, 2));
                        @endphp
                        <x-dashboard.card :padding="false" class="overflow-hidden">
                            <a href="{{ route('dashboard.services.product', $product->slug) }}" class="relative block aspect-[2/1] bg-muted">
                                @if($heroUrl)
                                    <img src="{{ $heroUrl }}" alt="" class="h-full w-full object-contain">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/40 via-muted to-elevated">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/15 text-xs font-bold text-white" aria-hidden="true">
                                            {{ $initials }}
                                        </span>
                                    </div>
                                @endif
                            </a>
                            <div class="space-y-2 p-4">
                                <div class="font-semibold text-text-primary">{{ $product->title }}</div>
                                @if(filled($product->short_description))
                                    <p class="text-sm text-text-secondary line-clamp-2">{{ $product->short_description }}</p>
                                @endif
                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <span class="font-semibold text-primary">From ₦{{ number_format($product->displayPrice(), 0) }}</span>
                                    <x-dashboard.button :href="route('dashboard.services.product', $product->slug)" size="xs">View</x-dashboard.button>
                                </div>
                            </div>
                        </x-dashboard.card>
                    @endforeach
                </div>
                <div class="mt-4">
                    <x-dashboard.pagination :paginator="$products" />
                </div>
            @endif
        @endif
    </div>
</x-layout.page>
@endsection
