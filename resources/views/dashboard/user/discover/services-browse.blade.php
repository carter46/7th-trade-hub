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
                    @endphp
                    @include('dashboard.user.partials.catalog-grid-card', [
                        'href' => $card['href'] ?? '#',
                        'label' => $label,
                        'description' => $card['short_description'] ?? null,
                        'imageSrc' => $imageSrc,
                        'meta' => $card['meta'] ?? null,
                        'ctaLabel' => $card['cta'] ?? 'View products',
                    ])
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
