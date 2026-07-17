@extends('layouts.marketing')

@section('title', 'Website Listings | 7th Trade Hub')

@section('content')
<section class="py-14 sm:py-20">
    <div class="max-w-marketing mx-auto px-5 sm:px-6">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <h1 class="text-3xl sm:text-4xl font-bold font-display mb-3">Website Listings</h1>
            <p class="text-slate-400">Hosted packages and templates you can launch with duration plans.</p>
        </div>

        <div class="mb-8 flex justify-center">
            @include('partials.catalog.search-filters', [
                'action' => route('website-listings'),
                'filters' => $filters,
                'types' => [],
                'categories' => $categories,
                'showIndustry' => true,
                'showFramework' => true,
            ])
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($products as $product)
                @include('partials.catalog.product-card', ['product' => $product])
            @empty
                <p class="text-slate-400 col-span-full">No website packages match your filters.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    </div>
</section>
@endsection
