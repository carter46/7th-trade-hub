@extends('layouts.marketing')

@section('title', $product->title.' | 7th Trade Hub')

@section('content')
<section class="py-8 sm:py-12">
    <div class="max-w-marketing mx-auto px-5 sm:px-6">
        @php
            $crumbs = [
                ['label' => 'Home', 'href' => route('home')],
                ['label' => 'Services', 'href' => route('services')],
            ];
            if (!empty($groupSlug) && !empty($groupContent)) {
                $crumbs[] = ['label' => $groupContent['label'], 'href' => route('services.segment', $groupSlug)];
            }
            if (!empty($typeKey) && !empty($typeContent)) {
                $crumbs[] = ['label' => $typeContent['label'], 'href' => route('services.segment', $typeKey)];
            }
            $crumbs[] = ['label' => $product->title];
        @endphp
        @include('partials.marketing.page-header', [
            'breadcrumbs' => $crumbs,
            'title' => $product->title,
            'subtitle' => $product->product_type->label(),
        ])

        <div class="grid lg:grid-cols-2 gap-10">
            <div class="aspect-video rounded-2xl bg-slate-900/70 overflow-hidden flex items-center justify-center">
                @if($product->hero_image)
                    <img src="{{ asset($product->hero_image) }}" alt="" class="w-full h-full object-cover">
                @else
                    <x-ui.icon :name="$product->product_type->icon()" class="w-16 h-16 text-accent" />
                @endif
            </div>
            <div>
                <p class="text-slate-400 mb-6">{{ $product->description }}</p>
                @if($product->activeVariants->isNotEmpty())
                    <ul class="text-sm text-slate-300 mb-4 space-y-1">
                        @foreach($product->activeVariants as $variant)
                            <li>{{ $variant->displayLabel() }} — ₦{{ number_format($variant->price, 2) }}</li>
                        @endforeach
                    </ul>
                @endif
                <p class="text-2xl font-bold mb-6">From ₦{{ number_format($product->displayPrice(), 2) }}</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('checkout.platform.show', $product->slug) }}" class="inline-flex px-6 py-3 rounded-xl bg-primary hover:bg-accent font-bold transition-colors">Buy now</a>
                    @auth
                        <form method="POST" action="{{ route('favorites.toggle') }}">
                            @csrf
                            <input type="hidden" name="type" value="platform_product">
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            <button type="submit" class="inline-flex px-6 py-3 rounded-xl border border-white/15 hover:border-accent/40 font-semibold transition-colors" aria-pressed="{{ ($isFavorited ?? false) ? 'true' : 'false' }}">
                                {{ ($isFavorited ?? false) ? 'Favorited' : 'Favorite' }}
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>

        <div class="mt-14 grid md:grid-cols-2 gap-8">
            @foreach([
                'Features' => $product->features,
                'Requirements' => $product->requirements,
                "What's included" => $product->whats_included,
            ] as $heading => $items)
                <div class="glassmorphism rounded-2xl p-6">
                    <h2 class="font-bold text-lg mb-3">{{ $heading }}</h2>
                    <ul class="space-y-2 text-sm text-slate-300">
                        @forelse(($items ?? []) as $item)
                            <li class="flex gap-2"><span class="text-accent">•</span>{{ $item }}</li>
                        @empty
                            <li class="text-slate-500">No details yet.</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
            <div class="glassmorphism rounded-2xl p-6 md:col-span-2">
                <h2 class="font-bold text-lg mb-3">Support</h2>
                <p class="text-sm text-slate-300 mb-6">{{ $product->support_text }}</p>
                <h2 class="font-bold text-lg mb-3">FAQs</h2>
                <div class="space-y-3">
                    @forelse(($product->faqs ?? []) as $faq)
                        <details class="rounded-xl border border-white/10 p-4">
                            <summary class="font-semibold cursor-pointer">{{ $faq['q'] ?? '' }}</summary>
                            <p class="text-sm text-slate-400 mt-2">{{ $faq['a'] ?? '' }}</p>
                        </details>
                    @empty
                        <p class="text-slate-500 text-sm">No FAQs yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
