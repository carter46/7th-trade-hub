@extends('layouts.marketing')

@section('title', $product->title.' | Website')

@section('content')
<section class="py-14 sm:py-20">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 space-y-10">
        <div class="grid lg:grid-cols-2 gap-10">
            <div>
                @php
                    $heroUrl = $product->heroMedia?->url('medium') ?? ($product->hero_image ? asset($product->hero_image) : null);
                @endphp
                <div class="aspect-video rounded-2xl bg-slate-900/70 overflow-hidden mb-4">
                    @if($heroUrl)
                        <img src="{{ $heroUrl }}" alt="" class="w-full h-full object-cover">
                    @elseif($product->images->first())
                        <img src="{{ asset($product->images->first()->path) }}" alt="" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center"><x-ui.icon name="inventory" class="w-16 h-16 text-accent" /></div>
                    @endif
                </div>
                @if($product->images->isNotEmpty())
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($product->images as $image)
                            <img src="{{ asset($image->path) }}" alt="{{ $image->alt }}" class="rounded-xl aspect-video object-cover border border-white/10">
                        @endforeach
                    </div>
                @endif
            </div>
            <div>
                <h1 class="text-3xl font-bold font-display mb-3">{{ $product->title }}</h1>
                <p class="text-slate-400 mb-4">{{ $product->description }}</p>
                <div class="flex flex-wrap gap-3 text-xs text-slate-300 mb-6">
                    @if($product->industry)<span class="px-2 py-1 rounded-lg bg-white/5">{{ $product->industry }}</span>@endif
                    @if($product->framework)<span class="px-2 py-1 rounded-lg bg-white/5">{{ $product->framework }}</span>@endif
                    @if($product->is_responsive)<span class="px-2 py-1 rounded-lg bg-white/5">Responsive</span>@endif
                    @if($product->is_seo_ready)<span class="px-2 py-1 rounded-lg bg-white/5">SEO ready</span>@endif
                    @if($product->support_period)<span class="px-2 py-1 rounded-lg bg-white/5">Support {{ $product->support_period }}</span>@endif
                </div>
                <p class="text-2xl font-bold mb-6">From ₦{{ number_format($product->displayPrice(), 2) }}</p>
                <div class="flex flex-wrap gap-3">
                    @if($product->demo_url)
                        <a href="{{ $product->demo_url }}" target="_blank" rel="noopener" class="px-5 py-3 rounded-xl border border-white/15 font-bold hover:bg-white/5">Test site</a>
                    @endif
                    <a href="{{ route('checkout.platform.show', $product->slug) }}" class="px-5 py-3 rounded-xl bg-primary hover:bg-accent font-bold">Buy now</a>
                    @auth
                        <form method="POST" action="{{ route('favorites.toggle') }}">
                            @csrf
                            <input type="hidden" name="type" value="platform_product">
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            <button type="submit" class="px-5 py-3 rounded-xl border border-white/15 font-semibold hover:border-accent/40" aria-pressed="{{ ($isFavorited ?? false) ? 'true' : 'false' }}">
                                {{ ($isFavorited ?? false) ? 'Favorited' : 'Favorite' }}
                            </button>
                        </form>
                    @endauth
                </div>
                @if($product->demo_username)
                    <div class="mt-6 glassmorphism rounded-xl p-4 text-sm">
                        <p class="font-semibold mb-2">Test credentials</p>
                        <p>User: <code class="text-accent">{{ $product->demo_username }}</code></p>
                        <p>Password: <code class="text-accent">{{ $product->demo_password }}</code></p>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            @foreach([
                'Features' => $product->features,
                'Requirements' => $product->requirements,
                "What's included" => $product->whats_included,
            ] as $heading => $items)
                <div class="glassmorphism rounded-2xl p-6">
                    <h2 class="font-bold mb-3">{{ $heading }}</h2>
                    <ul class="space-y-2 text-sm text-slate-300">
                        @foreach(($items ?? []) as $item)
                            <li>• {{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
            <div class="glassmorphism rounded-2xl p-6">
                <h2 class="font-bold mb-3">Support</h2>
                <p class="text-sm text-slate-300">{{ $product->support_text }}</p>
            </div>
            <div class="glassmorphism rounded-2xl p-6 md:col-span-2">
                <h2 class="font-bold mb-3">FAQs</h2>
                @foreach(($product->faqs ?? []) as $faq)
                    <details class="border-b border-white/10 py-3" @if(! empty($faq['open'])) open @endif>
                        <summary class="cursor-pointer font-semibold">{{ $faq['q'] ?? '' }}</summary>
                        <p class="text-sm text-slate-400 mt-2">{{ $faq['a'] ?? '' }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
