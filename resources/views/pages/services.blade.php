@extends('layouts.marketing')

@section('title', 'Services | 7th Trade Hub')

@section('content')
<section class="py-8 sm:py-12">
    <div class="max-w-marketing mx-auto px-5 sm:px-6">
        @include('partials.marketing.page-header', [
            'breadcrumbs' => [
                ['label' => 'Home', 'href' => route('home')],
                ['label' => 'Services'],
            ],
            'title' => 'Services',
            'subtitle' => 'Browse by category — network, communication, social, websites, documents, and escrow.',
        ])

        <form method="GET" action="{{ route('services') }}" class="mb-10">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label for="services-q" class="sr-only">Search services</label>
                    <input id="services-q" type="search" name="q" value="{{ $q }}" placeholder="Search services (e.g. TikTok, VPN…)"
                           class="w-full rounded-xl border border-white/10 bg-slate-900/50 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-accent/50 focus:outline-none focus:ring-1 focus:ring-accent/40">
                </div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-white text-slate-900 hover:bg-accent hover:text-white font-semibold text-sm transition-colors">Search</button>
            </div>
        </form>

        @if($searchResults !== null)
            <div class="mb-12">
                <h2 class="text-xl font-bold font-display mb-4">Search results</h2>
                @if($searchResults->isEmpty())
                    <p class="text-slate-400">No services match “{{ $q }}”. Try another term or browse a category below.</p>
                @else
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($searchResults as $product)
                            @include('partials.catalog.product-card', ['product' => $product])
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $searchResults->links() }}</div>
                @endif
            </div>
        @endif

        <h2 class="text-xl font-bold font-display mb-5">Browse categories</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($groups as $card)
                @include('partials.catalog.explore-card', ['card' => $card])
            @endforeach
        </div>
    </div>
</section>
@endsection
