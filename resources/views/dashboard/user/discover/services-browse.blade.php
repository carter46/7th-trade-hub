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
                    <x-dashboard.card>
                        <div class="font-semibold text-text-primary">{{ $card['label'] ?? $card['slug'] }}</div>
                        @if(!empty($card['short_description']))
                            <p class="mt-1 text-sm text-text-secondary line-clamp-2">{{ $card['short_description'] }}</p>
                        @endif
                        @if(!empty($card['meta']))
                            <p class="mt-1 text-xs text-text-muted">{{ $card['meta'] }}</p>
                        @endif
                        <a href="{{ $card['href'] }}" class="text-sm text-primary mt-3 inline-block">{{ $card['cta'] ?? 'View products' }} →</a>
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
                    <x-dashboard.button type="submit" icon="search">Search</x-dashboard.button>
                </form>
            </x-dashboard.card>

            @if(!$products || $products->isEmpty())
                <p class="text-text-muted">No products found.</p>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($products as $product)
                        <x-dashboard.card>
                            <div class="font-semibold text-text-primary">{{ $product->title }}</div>
                            @if(filled($product->short_description))
                                <p class="mt-1 text-sm text-text-secondary line-clamp-2">{{ $product->short_description }}</p>
                            @endif
                            <div class="mt-3 flex items-center justify-between gap-2">
                                <span class="font-semibold text-primary">₦{{ number_format($product->displayPrice(), 0) }}</span>
                                <a href="{{ route('dashboard.services.product', $product->slug) }}" class="text-sm text-primary">View →</a>
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
