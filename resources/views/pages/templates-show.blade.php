@extends('layouts.marketing')

@section('title', $product->title.' | Templates')

@section('content')
<section class="py-14 sm:py-20">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 grid lg:grid-cols-2 gap-10">
        <div class="aspect-[4/3] rounded-2xl bg-slate-900/70 overflow-hidden flex items-center justify-center">
            @if($product->hero_image)
                <img src="{{ asset($product->hero_image) }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
            @else
                <x-ui.icon name="listings" class="w-16 h-16 text-accent" />
            @endif
        </div>
        <div>
            <p class="text-sm text-accent mb-2">{{ $product->category?->name ?? 'Document' }}</p>
            <h1 class="text-3xl font-bold font-display mb-3">{{ $product->title }}</h1>
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
                <a href="{{ route('checkout.platform.show', $product->slug) }}" class="px-6 py-3 rounded-xl bg-primary hover:bg-accent font-bold">Buy now</a>
                @auth
                    <form method="POST" action="{{ route('favorites.toggle') }}">
                        @csrf
                        <input type="hidden" name="type" value="platform_product">
                        <input type="hidden" name="id" value="{{ $product->id }}">
                        <button type="submit" class="px-6 py-3 rounded-xl border border-white/15 font-semibold hover:border-accent/40" aria-pressed="{{ ($isFavorited ?? false) ? 'true' : 'false' }}">
                            {{ ($isFavorited ?? false) ? 'Favorited' : 'Favorite' }}
                        </button>
                    </form>
                @endauth
                <button type="button" disabled class="px-6 py-3 rounded-xl border border-white/15 text-slate-400 cursor-not-allowed" title="Coming in Phase 2">Edit template</button>
            </div>
            <p class="text-xs text-slate-500 mt-3">PDF/JPEG export and the template editor ship in Phase 2.</p>
        </div>
    </div>

    <div class="max-w-marketing mx-auto px-5 sm:px-6 mt-14 grid md:grid-cols-2 gap-8">
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
</section>
@endsection
