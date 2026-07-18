@extends('layouts.marketing')
@section('title', 'Marketplace')
@section('meta_description', 'Discover digital products and online services from trusted vendors with escrow protection.')
@section('content')
@php
    $tree = $parents->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'children' => $p->children->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values(),
    ])->values();
@endphp
<section class="max-w-marketing mx-auto px-5 sm:px-6 py-8 sm:py-12">
    @include('partials.marketing.page-header', [
        'breadcrumbs' => [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Marketplace'],
        ],
        'title' => 'Marketplace',
        'subtitle' => 'Discover digital products and online services from trusted vendors. Every eligible purchase is protected through our secure escrow system.',
    ])

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <p class="text-sm text-text-secondary">Want to be a vendor?</p>
        <a href="{{ route('dashboard.listings.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-primary hover:bg-accent font-semibold text-sm transition-colors">Sell Now</a>
    </div>

    <form method="GET" class="mb-8" x-data="listingCategoryForm(@js($tree), {{ (int) ($filters['parent'] ?? 0) }}, {{ (int) ($filters['category'] ?? 0) }})">
        <x-ui.card>
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <x-ui.input label="Search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search listings..." />
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-sm font-medium text-text-secondary mb-1">Parent category</label>
                    <select name="parent" x-model.number="parentId" class="w-full rounded-xl border-border-default bg-elevated">
                        <option value="0">All groups</option>
                        <template x-for="parent in parents" :key="parent.id">
                            <option :value="parent.id" x-text="parent.name"></option>
                        </template>
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-sm font-medium text-text-secondary mb-1">Subcategory</label>
                    <select name="category" x-model.number="categoryId" class="w-full rounded-xl border-border-default bg-elevated">
                        <option value="0">All subcategories</option>
                        <template x-for="child in children" :key="child.id">
                            <option :value="child.id" x-text="child.name"></option>
                        </template>
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <x-ui.select label="Sort" name="sort">
                        <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                        <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price: High to Low</option>
                    </x-ui.select>
                </div>
                <label class="flex items-center gap-2 text-text-secondary text-sm pb-2">
                    <input type="checkbox" name="featured" value="1" @checked(!empty($filters['featured'])) class="rounded border-border-default">
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
                @if($listing->user)
                    <p class="text-text-muted text-xs mt-0.5">by {{ $listing->user->name }}</p>
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
