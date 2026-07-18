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
    $filtersExpanded = filled($filters['parent'] ?? null)
        || filled($filters['category'] ?? null)
        || filled($filters['sort'] ?? null) && ($filters['sort'] ?? 'newest') !== 'newest'
        || ! empty($filters['featured']);
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
    <form
        method="GET"
        class="mb-8"
        x-data="listingCategoryForm(@js($tree), {{ (int) ($filters['parent'] ?? 0) }}, {{ (int) ($filters['category'] ?? 0) }}, {{ $filtersExpanded ? 'true' : 'false' }})"
    >
        <x-ui.card>
            <div class="flex items-end gap-2 sm:gap-3">
                <div class="flex-1 min-w-0">
                    <x-ui.input label="Search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search listings..." />
                </div>
                <button
                    type="button"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border-default bg-elevated text-text-primary hover:border-accent/50 hover:text-accent transition-colors mb-0.5"
                    @click="filtersOpen = !filtersOpen"
                    :aria-expanded="filtersOpen.toString()"
                    aria-controls="marketplace-advanced-filters"
                    :aria-label="filtersOpen ? 'Collapse filters' : 'Expand filters'"
                >
                    <span x-show="!filtersOpen"><x-ui.icon name="plus" class="w-5 h-5" /></span>
                    <span x-show="filtersOpen" x-cloak><x-ui.icon name="minus" class="w-5 h-5" /></span>
                </button>
                <div class="shrink-0 mb-0.5">
                    <x-ui.button type="submit" size="md">Apply</x-ui.button>
                </div>
            </div>

            <div
                id="marketplace-advanced-filters"
                class="mt-4 pt-4 border-t border-border-default"
                x-show="filtersOpen"
                x-cloak
            >
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="min-w-[160px] flex-1">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Parent category</label>
                        <select name="parent" x-model.number="parentId" class="w-full rounded-xl border-border-default bg-elevated">
                            <option value="0">All groups</option>
                            <template x-for="parent in parents" :key="parent.id">
                                <option :value="parent.id" x-text="parent.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="min-w-[160px] flex-1">
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
                </div>
            </div>
        </x-ui.card>
    </form>

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
