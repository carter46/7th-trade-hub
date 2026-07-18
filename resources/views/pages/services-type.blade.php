@extends('layouts.marketing')

@section('title', ($content['label'] ?? 'Services').' | 7th Trade Hub')

@section('content')
<section class="py-8 sm:py-12">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 space-y-10">
        @php
            $crumbs = [
                ['label' => 'Home', 'href' => route('home')],
                ['label' => 'Services', 'href' => route('services')],
            ];
            if ($groupSlug && $groupContent) {
                $crumbs[] = ['label' => $groupContent['label'], 'href' => route('services.segment', $groupSlug)];
            }
            $typeLabel = config('catalog.types.'.$typeKey.'.label', $typeKey);
            if (!empty($activeCategory)) {
                $crumbs[] = ['label' => $typeLabel, 'href' => route('services.segment', $typeKey)];
                $crumbs[] = ['label' => $activeCategory->name];
            } else {
                $crumbs[] = ['label' => $content['label'] ?? $typeLabel];
            }
        @endphp

        @include('partials.marketing.page-header', [
            'breadcrumbs' => $crumbs,
            'title' => $content['hero_title'] ?? $content['label'],
            'subtitle' => $content['hero_subtitle'] ?? $content['short_description'],
            'image' => $content['banner_image'] ?? null,
        ])

        @if($content['short_description'] && ($content['short_description'] !== ($content['hero_subtitle'] ?? null)))
            <p class="text-slate-300 max-w-3xl">{{ $content['short_description'] }}</p>
        @endif

        @if(!empty($content['benefits']))
            <div>
                <h2 class="text-lg font-bold font-display mb-3">Benefits</h2>
                <ul class="grid sm:grid-cols-2 gap-2 text-sm text-slate-300">
                    @foreach($content['benefits'] as $benefit)
                        <li class="flex gap-2"><span class="text-accent">•</span><span>{{ $benefit }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($featured->isNotEmpty())
            <div>
                <h2 class="text-xl font-bold font-display mb-4">Featured</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($featured as $product)
                        @include('partials.catalog.product-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Popular / Most Purchased: coming in a later pass --}}

        <div>
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
                <h2 class="text-xl font-bold font-display">All {{ $content['label'] }}</h2>
                <form method="GET" action="{{ route('services.segment', $typeKey) }}" class="flex flex-wrap gap-3 items-end">
                    <div class="min-w-[160px]">
                        <label class="block text-xs text-slate-400 mb-1">Category</label>
                        <select name="category" class="w-full rounded-xl border border-white/10 bg-slate-900/50 px-3 py-2 text-sm">
                            <option value="">All</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(($filters['category'] ?? null) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[180px] flex-1">
                        <label class="block text-xs text-slate-400 mb-1">Search</label>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Filter…"
                               class="w-full rounded-xl border border-white/10 bg-slate-900/50 px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-xl border border-white/15 hover:border-accent/40 text-sm font-semibold transition-colors">Apply</button>
                </form>
            </div>

            @if($products->isEmpty())
                <p class="text-slate-400">No services match your filters.</p>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($products as $product)
                        @include('partials.catalog.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </div>

        @if(!empty($content['faq']))
            <div>
                <h2 class="text-lg font-bold font-display mb-3">FAQ</h2>
                <dl class="space-y-4">
                    @foreach($content['faq'] as $item)
                        <div class="glassmorphism rounded-xl p-4">
                            <dt class="font-semibold mb-1">{{ $item['q'] ?? '' }}</dt>
                            <dd class="text-sm text-slate-400">{{ $item['a'] ?? '' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </div>
</section>
@endsection
