@extends('layouts.marketing')

@section('title', 'Services | 7th Trade Hub')

@section('content')
<section class="py-14 sm:py-20">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 space-y-14">
        <div class="text-center max-w-2xl mx-auto">
            <h1 class="text-3xl sm:text-4xl font-bold font-display mb-3">Services</h1>
            <p class="text-slate-400">VPN, VPS, domains, documents, escrow, and more — sold directly by 7th Trade Hub.</p>
        </div>

        @unless(request()->filled('type') || request()->filled('q') || request()->filled('category') || request()->filled('division'))
            <div>
                <h2 class="text-xl font-bold mb-4 font-display">Browse by division</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($divisions as $key => $division)
                        <a href="{{ route('services', ['division' => $key]) }}" class="glassmorphism p-5 rounded-2xl hover:border-accent/40 transition-all block">
                            <h3 class="font-bold mb-1">{{ $division['label'] }}</h3>
                            <p class="text-sm text-slate-400">{{ $division['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endunless

        @unless(request()->filled('type') || request()->filled('q') || request()->filled('category'))
            @if($featured->isNotEmpty())
                <div>
                    <h2 class="text-xl font-bold mb-4 font-display">Featured</h2>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($featured as $product)
                            @include('partials.catalog.product-card', ['product' => $product])
                        @endforeach
                    </div>
                </div>
            @endif

            @if($categories->isNotEmpty())
                <div>
                    <h2 class="text-xl font-bold mb-4 font-display">Categories</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($categories as $category)
                            <a href="{{ route('services', array_filter(['category' => $category->id, 'division' => $filters['division'] ?? null, 'type' => $category->product_type->value])) }}"
                               class="px-4 py-2 rounded-xl border border-white/10 text-sm hover:border-accent/40 transition-colors">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endunless

        <div>
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
                <h2 class="text-xl font-bold font-display">All services</h2>
                @include('partials.catalog.search-filters', [
                    'action' => route('services'),
                    'filters' => $filters,
                    'types' => $types,
                    'categories' => $categories,
                ])
            </div>

            @if($products->isEmpty())
                <p class="text-slate-400">No services match your filters. Try clearing search or choosing another category.</p>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($products as $product)
                        @include('partials.catalog.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
