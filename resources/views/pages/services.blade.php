@extends('layouts.marketing')

@section('title', 'Services | 7th Trade Hub')

@section('content')
@php
    $heroImage = asset('assets/images/services_1.jpg');
    $groupCount = $groups->count();
@endphp

{{-- Hero (marketing layout; site header/footer unchanged) --}}
<section class="relative min-h-[28rem] sm:min-h-[32rem] flex items-center justify-center overflow-hidden py-16 sm:py-20">
    <div class="absolute inset-0 z-0 opacity-40" aria-hidden="true">
        <div class="w-full h-full bg-cover bg-center mix-blend-screen" style="background-image: url('{{ $heroImage }}')"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-surface/80 via-transparent to-surface"></div>
    </div>
    <div class="pointer-events-none absolute inset-0 z-[1] bg-[radial-gradient(circle_at_center,rgba(11,106,57,0.12)_0%,transparent_70%)]" aria-hidden="true"></div>

    <div class="relative z-10 max-w-marketing mx-auto px-5 sm:px-6 text-center w-full">
        <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white mb-4 leading-tight">
            Secure Digital Services
        </h1>
        <p class="max-w-2xl mx-auto text-base sm:text-lg text-text-secondary mb-8 leading-relaxed">
            Browse by category — network, communication, social, websites, documents, and escrow. Explore our enterprise-grade infrastructure built for the next generation of trade.
        </p>

        <form method="GET" action="{{ route('services') }}" class="max-w-xl mx-auto relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-primary/40 to-accent/20 rounded-xl blur opacity-30 group-focus-within:opacity-100 transition duration-500" aria-hidden="true"></div>
            <div class="relative flex items-center gap-2 bg-elevated rounded-xl p-2 border border-border-default">
                <span class="pl-2 text-text-muted shrink-0" aria-hidden="true">
                    <x-ui.icon name="search" class="w-5 h-5" />
                </span>
                <label for="services-q" class="sr-only">Search services</label>
                <input
                    id="services-q"
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Search services..."
                    class="w-full min-w-0 bg-transparent border-0 focus:ring-0 text-text-primary placeholder:text-text-muted px-2 py-2 text-sm sm:text-base"
                />
                <x-ui.button type="submit" variant="primary" size="md" class="shrink-0 !bg-primary hover:!bg-accent">
                    Search
                </x-ui.button>
            </div>
        </form>
    </div>
</section>

@if($searchResults !== null)
    <section class="py-12 sm:py-16 bg-surface border-t border-border-subtle">
        <div class="max-w-marketing mx-auto px-5 sm:px-6">
            <div class="flex items-center justify-between mb-8 border-b border-border-subtle pb-4">
                <h2 class="font-display text-2xl sm:text-3xl font-semibold text-white tracking-tight">Search results</h2>
            </div>
            @if($searchResults->isEmpty())
                <x-ui.empty
                    icon="search"
                    title="No matching services"
                    description="No services match “{{ $q }}”. Try another term or browse a category below."
                />
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($searchResults as $product)
                        @include('partials.catalog.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="mt-8">{{ $searchResults->links() }}</div>
            @endif
        </div>
    </section>
@endif

{{-- Category grid --}}
<section class="py-16 sm:py-20 bg-surface">
    <div class="max-w-marketing mx-auto px-5 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-8 border-b border-border-subtle pb-4">
            <h2 class="font-display text-2xl sm:text-3xl font-semibold text-white tracking-tight">Browse Categories</h2>
            <span class="text-xs font-medium text-text-secondary bg-elevated px-3 py-1 rounded-full border border-border-subtle">
                Showing {{ $groupCount }} {{ \Illuminate\Support\Str::plural('Category', $groupCount) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @foreach($groups as $card)
                @include('partials.catalog.explore-card', ['card' => $card])
            @endforeach
        </div>
    </div>
</section>

{{-- Highlights (live catalog stats + platform benefits) --}}
<section class="py-12 sm:py-14 bg-surface border-t border-border-subtle">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 text-center">
        @foreach($highlights as $item)
            <div>
                <div class="font-display text-2xl sm:text-3xl font-bold text-accent mb-1">{{ $item['value'] }}</div>
                <div class="font-display text-sm sm:text-base font-semibold text-white">{{ $item['label'] }}</div>
                <p class="text-text-secondary text-xs sm:text-sm mt-1.5 leading-relaxed">{{ $item['blurb'] }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
