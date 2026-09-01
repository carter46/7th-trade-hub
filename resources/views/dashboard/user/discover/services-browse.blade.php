@extends('layouts.dashboard-user')

@section('title', $title)

@section('content')
<x-layout.page
    :title="$title"
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Services', route('dashboard.services')],
        [$title, null],
    ]"
>
    <div class="space-y-6">
        @if(!empty($typeCards) && $typeCards->isNotEmpty())
            <x-dashboard.card-grid :count="$typeCards->count()">
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
                    <x-dashboard.card :padding="false" class="flex h-full flex-col overflow-hidden">
                        <div class="p-3">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-lg bg-muted">
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
                        </div>
                        <div class="flex flex-1 flex-col space-y-2 px-4 pb-4">
                            <div class="font-semibold text-text-primary">{{ $label }}</div>
                            @if(!empty($card['short_description']))
                                <p class="text-sm text-text-secondary line-clamp-2">{{ $card['short_description'] }}</p>
                            @endif
                            @if(!empty($card['meta']))
                                <p class="text-xs text-text-muted">{{ $card['meta'] }}</p>
                            @endif
                            @if(!empty($card['href']))
                                <div class="mt-auto pt-2">
                                    <x-dashboard.button :href="$card['href']" size="xs" class="w-full sm:w-auto">
                                        {{ $card['cta'] ?? 'View products' }}
                                    </x-dashboard.button>
                                </div>
                            @endif
                        </div>
                    </x-dashboard.card>
                @endforeach
            </x-dashboard.card-grid>
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
                <x-dashboard.card-grid :count="$products->count()">
                    @foreach($products as $product)
                        @include('dashboard.user.partials.service-product-card', ['product' => $product])
                    @endforeach
                </x-dashboard.card-grid>
                <div class="mt-4">
                    <x-dashboard.pagination :paginator="$products" />
                </div>
            @endif
        @endif
    </div>
</x-layout.page>
@endsection
