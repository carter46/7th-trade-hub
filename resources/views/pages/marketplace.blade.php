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

@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Marketplace'],
    ],
    'title' => 'Marketplace',
    'subtitle' => 'Discover digital products and online services from trusted vendors. Every eligible purchase is protected through our secure escrow system.',
    'cta' => [
        'label' => 'Sell Now',
        'href' => route('dashboard.listings.create'),
    ],
])

<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-12 sm:pb-16">
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

    {{-- Flex layout (not 12-col grid) so side-by-side works even if grid-cols-12 is missing from CSS build --}}
    <div class="flex flex-col lg:flex-row lg:gap-8 lg:items-start">
        <div class="w-full min-w-0 lg:flex-1">
            <div class="space-y-4">
                @forelse($listings as $listing)
                    @include('partials.marketplace.listing-card', ['listing' => $listing])
                @empty
                    <x-ui.empty
                        icon="listings"
                        title="No listings match your filters"
                        description="Try a different search or clear filters to see more results."
                    />
                @endforelse
            </div>
            <div class="mt-8">
                <x-ui.pagination :paginator="$listings" />
            </div>
        </div>

        <div class="w-full mt-10 lg:mt-0 lg:w-80 xl:w-96 shrink-0 lg:sticky lg:top-24">
            @include('partials.marketplace.platform-sidebar')
        </div>
    </div>
</section>
@endsection
