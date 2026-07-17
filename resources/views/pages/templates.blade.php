@extends('layouts.marketing')

@section('title', 'Document Templates | 7th Trade Hub')

@section('content')
<section class="py-14 sm:py-20">
    <div class="max-w-marketing mx-auto px-5 sm:px-6">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <h1 class="text-3xl sm:text-4xl font-bold font-display mb-3">Document Templates</h1>
            <p class="text-slate-400">Contracts, HR packs, and business docs you can buy and use.</p>
        </div>

        <div class="mb-8 flex justify-center">
            @include('partials.catalog.search-filters', [
                'action' => route('templates'),
                'filters' => $filters,
                'types' => [],
                'categories' => $categories,
            ])
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($products as $product)
                @include('partials.catalog.product-card', ['product' => $product])
            @empty
                <p class="text-slate-400 col-span-full">No templates match your filters.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    </div>
</section>
@endsection
