@extends('layouts.marketing')
@section('title', 'Marketplace')
@section('meta_description', 'Browse and buy digital products and services on 7th Trade Hub. Escrow-protected NGN payments.')
@section('content')
<section class="max-w-marketing mx-auto px-5 sm:px-6 py-16">
    <h1 class="text-4xl font-bold text-text-primary mb-8">Marketplace</h1>

    <form method="GET" class="mb-8">
        <x-ui.card>
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <x-ui.input label="Search" name="q" value="{{ request('q') }}" placeholder="Search listings..." />
                </div>
                <div class="min-w-[160px]">
                    <x-ui.select label="Category" name="category">
                        <option value="">All categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <div class="min-w-[140px]">
                    <x-ui.select label="Sort" name="sort">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                    </x-ui.select>
                </div>
                <label class="flex items-center gap-2 text-text-secondary text-sm pb-2">
                    <input type="checkbox" name="featured" value="1" @checked(request()->boolean('featured')) class="rounded border-border-default">
                    Featured only
                </label>
                <x-ui.button type="submit">Apply</x-ui.button>
            </div>
        </x-ui.card>
    </form>

    <div class="grid md:grid-cols-3 gap-6">
        @forelse($listings as $listing)
            <x-ui.card class="flex flex-col">
                @if($listing->featured)
                    <x-ui.badge status="warning">Featured</x-ui.badge>
                @endif
                <h2 class="text-xl font-bold text-text-primary mt-2">{{ $listing->title }}</h2>
                @if($listing->listingCategory)
                    <p class="text-text-muted text-xs mt-1">{{ $listing->listingCategory->name }}</p>
                @endif
                <p class="text-text-secondary mt-2 text-sm flex-1">{{ Str::limit($listing->description, 120) }}</p>
                <p class="text-accent font-bold mt-4">₦{{ number_format($listing->price, 2) }}</p>
                <x-ui.button :href="route('marketplace.show', $listing->slug)" variant="link" class="mt-4 !justify-start px-0">View details</x-ui.button>
            </x-ui.card>
        @empty
            <div class="md:col-span-3">
                <x-ui.empty
                    icon="listings"
                    title="No listings match your filters"
                    description="Try a different search or clear filters to see more results."
                />
            </div>
        @endforelse
    </div>
    <div class="mt-8">
        <x-ui.pagination :paginator="$listings" />
    </div>
</section>
@endsection
