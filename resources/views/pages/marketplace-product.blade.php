@extends('layouts.marketing')

@php
    $pageTitle = $content['seo_title'] ?: ($content['og_title'] ?: ($content['hero_title'] ?? $content['label']));
    $metaDescription = $content['seo_description'] ?: ($content['og_description'] ?: ($content['short_description'] ?? $content['hero_subtitle']));
@endphp

@section('title', $pageTitle.' | 7th Trade Hub')
@section('meta_description', $metaDescription)
@section('og_image', $content['og_image'] ?? '')

@section('content')
@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Marketplace', 'href' => route('marketplace')],
        ['label' => $categoryContent['label'], 'href' => route('marketplace.show', $category->slug)],
        ['label' => $content['label']],
    ],
    'title' => $content['hero_title'] ?? $content['label'],
    'subtitle' => $content['hero_subtitle'] ?? $content['short_description'],
    'image' => $content['banner_image'] ?? null,
])

<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-12 sm:pb-16 space-y-10">
    @if(! empty($content['benefits']))
        <div class="rounded-xl border border-white/10 bg-slate-900/40 p-6">
            <h2 class="text-lg font-bold font-display mb-4">What you can find here</h2>
            <ul class="grid sm:grid-cols-2 gap-3 text-sm text-slate-300">
                @foreach($content['benefits'] as $benefit)
                    <li class="flex items-start gap-2">
                        <x-ui.icon name="check" class="w-4 h-4 text-accent shrink-0 mt-0.5" />
                        <span>{{ $benefit }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <h2 class="text-xl font-bold font-display">{{ $content['label'] }} listings</h2>
            <form method="GET" action="{{ route('marketplace.product', ['category' => $category->slug, 'product' => $marketplaceProduct->slug]) }}" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-[180px] flex-1">
                    <label class="block text-xs text-slate-400 mb-1">Search</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Filter listings…"
                           class="w-full rounded-xl border border-white/10 bg-slate-900/50 px-3 py-2 text-sm text-white placeholder:text-slate-500">
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs text-slate-400 mb-1">Sort</label>
                    <select name="sort" class="w-full rounded-xl border border-white/10 bg-slate-900/50 px-3 py-2 text-sm text-white">
                        <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                        <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price: High to Low</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-300 pb-2">
                    <input type="checkbox" name="featured" value="1" @checked(! empty($filters['featured'])) class="rounded border-white/20">
                    Featured only
                </label>
                <button type="submit" class="px-4 py-2 rounded-xl bg-white text-slate-900 hover:bg-accent hover:text-white text-sm font-semibold transition-colors">Apply</button>
            </form>
        </div>

        @include('partials.marketplace.listings-results', ['listings' => $listings])
    </div>

    @if(! empty($content['faq']))
        <div class="space-y-4">
            <h2 class="text-lg font-bold font-display">Frequently asked questions</h2>
            <div class="space-y-3">
                @foreach($content['faq'] as $faq)
                    @if(! empty($faq['q']))
                        <details class="rounded-xl border border-white/10 bg-slate-900/40 p-4" @if(! empty($faq['open'])) open @endif>
                            <summary class="font-semibold cursor-pointer">{{ $faq['q'] }}</summary>
                            <p class="text-sm text-slate-400 mt-2">{{ $faq['a'] ?? '' }}</p>
                        </details>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
