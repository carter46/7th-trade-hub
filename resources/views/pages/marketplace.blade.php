@extends('layouts.marketing')
@section('title', 'Marketplace')
@section('meta_description', 'Discover digital products and online services from trusted vendors with escrow protection.')
@section('content')
@php
    $tree = $parents->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'products' => $p->products->map(fn ($prod) => ['id' => $prod->id, 'name' => $prod->name])->values(),
    ])->values();
    $filtersExpanded = filled($filters['category'] ?? null)
        || filled($filters['product'] ?? null)
        || (( $filters['sort'] ?? 'newest') !== 'newest')
        || ! empty($filters['featured']);
@endphp

@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Marketplace'],
    ],
    'title' => 'Marketplace',
    'subtitle' => 'Discover digital products and online services from trusted vendors. Every eligible purchase is protected through our secure escrow system.',
    'image' => 'assets/images/market_place.jpg',
    'cta' => [
        'label' => 'Sell Now',
        'href' => route('dashboard.listings.create'),
    ],
])

<section
    class="max-w-marketing mx-auto px-5 sm:px-6 pb-12 sm:pb-16"
    x-data="marketplaceBrowse({
        parents: @js($tree),
        categoryId: {{ (int) ($filters['category'] ?? 0) }},
        productId: {{ (int) ($filters['product'] ?? 0) }},
        q: @js($filters['q'] ?? ''),
        sort: @js($filters['sort'] ?? 'newest'),
        featured: {{ !empty($filters['featured']) ? 'true' : 'false' }},
        filtersOpen: {{ $filtersExpanded ? 'true' : 'false' }},
        indexUrl: @js(url('/marketplace')),
        suggestUrl: @js(url('/marketplace/suggestions')),
    })"
>
    <form method="GET" action="{{ route('marketplace') }}" class="mb-8" x-ref="filterForm" @submit="applyFilters($event)">
        <x-ui.card>
            <div
                id="marketplace-advanced-filters"
                class="mb-4 pb-4 border-b border-border-default"
                :class="filtersOpen ? 'block' : 'hidden lg:block'"
            >
                <div class="hidden lg:block mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Filters</p>
                </div>
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="min-w-[160px] flex-1">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Category</label>
                        <select name="category" x-model.number="categoryId" class="w-full rounded-xl border-border-default bg-elevated">
                            <option value="0">All categories</option>
                            <template x-for="parent in parents" :key="parent.id">
                                <option :value="parent.id" x-text="parent.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="min-w-[160px] flex-1">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Product</label>
                        <select name="product" x-model.number="productId" class="w-full rounded-xl border-border-default bg-elevated">
                            <option value="0">All products</option>
                            <template x-for="prod in products" :key="prod.id">
                                <option :value="prod.id" x-text="prod.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="min-w-[140px]">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Sort</label>
                        <select name="sort" x-model="sort" class="w-full rounded-xl border-border-default bg-elevated">
                            <option value="newest">Newest</option>
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-text-secondary text-sm pb-2">
                        <input type="checkbox" name="featured" value="1" x-model="featured" class="rounded border-border-default">
                        Featured only
                    </label>
                </div>
            </div>

            <div class="relative flex items-end gap-2 sm:gap-3">
                <div class="flex-1 min-w-0 relative">
                    <label class="block text-sm font-medium text-text-secondary mb-1" for="marketplace-q">Search</label>
                    <input
                        id="marketplace-q"
                        type="search"
                        name="q"
                        x-model="q"
                        @input="onSearchInput()"
                        @focus="if ((q || '').trim().length >= 2) showSuggest = true"
                        @blur="hideSuggestSoon()"
                        autocomplete="off"
                        placeholder="Search listings..."
                        class="block w-full rounded-lg border border-border-default bg-elevated/50 text-text-primary placeholder:text-text-muted focus-ring h-10 px-3 text-sm"
                    >
                    <div
                        x-show="showSuggest"
                        x-cloak
                        class="absolute left-0 right-0 top-full mt-1 z-30 rounded-xl border border-slate-200 bg-white shadow-xl overflow-hidden"
                    >
                        <template x-if="keywords.length">
                            <div class="px-3 py-2 border-b border-slate-100">
                                <p class="text-[10px] uppercase tracking-wide text-slate-400 mb-1.5">Related</p>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="word in keywords" :key="word">
                                        <button type="button" class="rounded-full bg-slate-100 hover:bg-primary/10 hover:text-primary px-2.5 py-1 text-xs text-slate-700" @mousedown.prevent="pickKeyword(word)" x-text="word"></button>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template x-if="suggestions.length">
                            <ul class="max-h-64 overflow-y-auto py-1">
                                <template x-for="item in suggestions" :key="item.slug">
                                    <li>
                                        <a :href="item.url" class="flex items-center justify-between gap-3 px-3 py-2.5 hover:bg-slate-50 text-left">
                                            <span class="min-w-0">
                                                <span class="block text-sm font-medium text-slate-900 truncate" x-text="item.title"></span>
                                                <span class="block text-xs text-slate-500" x-text="item.product || ''"></span>
                                            </span>
                                            <span class="shrink-0 text-xs font-semibold text-primary" x-text="'₦' + Number(item.price).toLocaleString()"></span>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </template>
                    </div>
                </div>

                <button
                    type="button"
                    class="lg:hidden inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border-default bg-elevated text-text-primary hover:border-accent/50 hover:text-accent transition-colors"
                    @click="filtersOpen = !filtersOpen"
                    :aria-expanded="filtersOpen.toString()"
                    aria-controls="marketplace-advanced-filters"
                    :aria-label="filtersOpen ? 'Collapse filters' : 'Expand filters'"
                >
                    <span x-show="!filtersOpen"><x-ui.icon name="plus" class="w-5 h-5" /></span>
                    <span x-show="filtersOpen" x-cloak><x-ui.icon name="minus" class="w-5 h-5" /></span>
                </button>

                <div class="shrink-0">
                    <x-ui.button type="submit" size="md" x-bind:disabled="loading">
                        <span class="inline-flex items-center gap-2">
                            <span x-show="loading" x-cloak><x-ui.icon name="spinner" class="w-4 h-4 animate-spin" /></span>
                            Apply
                        </span>
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>
    </form>

    <div class="flex flex-col lg:flex-row lg:gap-8 lg:items-start">
        <div class="w-full min-w-0 lg:flex-1 relative">
            <div
                x-show="loading"
                x-cloak
                class="absolute inset-0 z-10 rounded-xl bg-slate-950/30 backdrop-blur-[1px] flex items-start justify-center pt-16"
            >
                <span class="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm text-slate-700 shadow">
                    <x-ui.icon name="spinner" class="w-4 h-4 animate-spin" /> Updating…
                </span>
            </div>
            <div id="marketplace-results">
                @include('partials.marketplace.listings-results', ['listings' => $listings])
            </div>
        </div>

        <div class="w-full mt-10 lg:mt-0 lg:w-80 xl:w-96 shrink-0 lg:sticky lg:top-24">
            @include('partials.marketplace.platform-sidebar')
        </div>
    </div>
</section>

@push('scripts')
<script>
function marketplaceBrowse(config) {
    return {
        parents: config.parents,
        categoryId: config.categoryId,
        productId: config.productId,
        q: config.q,
        sort: config.sort,
        featured: config.featured,
        filtersOpen: config.filtersOpen,
        indexUrl: config.indexUrl,
        suggestUrl: config.suggestUrl,
        loading: false,
        showSuggest: false,
        suggestions: [],
        keywords: [],
        suggestTimeout: null,
        hideTimeout: null,
        
        get products() {
            const parent = this.parents.find(p => p.id === this.categoryId);
            return parent ? parent.products : [];
        },
        
        init() {
            this.$watch('categoryId', (newVal, oldVal) => {
                if (newVal !== oldVal) {
                    this.productId = 0;
                }
            });
        },
        
        async applyFilters(event) {
            event.preventDefault();
            this.loading = true;
            
            const form = this.$refs.filterForm;
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            
            const url = `${this.indexUrl}?${params.toString()}&ajax=1`;
            
            try {
                const response = await fetch(url);
                const data = await response.json();
                
                document.getElementById('marketplace-results').innerHTML = data.html;
                window.history.pushState({}, '', data.url);
            } catch (error) {
                console.error('Filter error:', error);
            } finally {
                this.loading = false;
            }
        },
        
        onSearchInput() {
            clearTimeout(this.suggestTimeout);
            
            if ((this.q || '').trim().length < 2) {
                this.showSuggest = false;
                this.suggestions = [];
                this.keywords = [];
                return;
            }
            
            this.suggestTimeout = setTimeout(() => this.fetchSuggestions(), 300);
        },
        
        async fetchSuggestions() {
            const url = `${this.suggestUrl}?q=${encodeURIComponent(this.q)}`;
            
            try {
                const response = await fetch(url);
                const data = await response.json();
                
                this.suggestions = data.suggestions || [];
                this.keywords = data.keywords || [];
                this.showSuggest = true;
            } catch (error) {
                console.error('Suggest error:', error);
            }
        },
        
        pickKeyword(word) {
            this.q = word;
            this.showSuggest = false;
            this.$refs.filterForm.submit();
        },
        
        hideSuggestSoon() {
            clearTimeout(this.hideTimeout);
            this.hideTimeout = setTimeout(() => {
                this.showSuggest = false;
            }, 200);
        }
    };
}
</script>
@endpush
@endsection
