@extends('layouts.marketing')

@section('title', $product->title)

@section('content')
@php
    $crumbs = [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Services', 'href' => route('services')],
    ];
    if (! empty($groupSlug) && ! empty($groupContent)) {
        $crumbs[] = ['label' => $groupContent['label'], 'href' => route('services.segment', $groupSlug)];
    }
    if (! empty($typeKey) && ! empty($typeContent)) {
        $typeLabel = $typeContent['label'] ?? $product->product_type?->label() ?? $typeKey;
        $lastGroup = $groupContent['label'] ?? null;
        if ($typeLabel !== $lastGroup) {
            $crumbs[] = [
                'label' => $typeLabel,
                'href' => $groupSlug
                    ? route('services.type', ['category' => $groupSlug, 'service' => $typeKey])
                    : route('services.segment', $typeKey),
            ];
        }
    }
    $crumbs[] = ['label' => $product->title];

    $heroUrl = $product->heroMedia?->url('medium') ?? ($product->hero_image ? asset($product->hero_image) : null);

    $gallery = collect();
    if ($heroUrl) {
        $gallery->push(['src' => $heroUrl, 'alt' => $product->title]);
    }
    foreach ($product->images ?? [] as $img) {
        $gallery->push([
            'src' => asset(ltrim($img->path, '/')),
            'alt' => $img->alt ?: $product->title,
        ]);
    }
    if ($gallery->isEmpty()) {
        $gallery->push([
            'src' => asset('assets/images/Image_ro410gro410gro41.png'),
            'alt' => $product->title,
        ]);
    }
    $gallery = $gallery->unique('src')->values();

    $subtitle = $product->short_description ?: null;
    $startPrice = $product->displayPrice();
    $checkoutUrl = route('dashboard.services.checkout', $product->slug);

    $variants = $product->activeVariants;
    $monthlyVariant = $variants->first(fn ($v) => (int) $v->duration_months === 1);
    $defaultVariant = $variants->firstWhere('is_default', true)
        ?? $variants->sortBy('price')->first();
    $showPerMonthSuffix = $defaultVariant && (int) $defaultVariant->duration_months === 1;
    $longestMonths = (int) ($variants->max('duration_months') ?: 0);
    $popularVariantId = optional(
        $variants->first(fn ($v) => (int) $v->duration_months === 3)
            ?? $variants->firstWhere('is_default', true)
    )->id;

    $heroBg = $product->hero_image ?: null;
@endphp

{{-- Compact hero: breadcrumbs only, same height/alignment as /services/* pages --}}
<header class="relative isolate overflow-hidden border-b border-white/10 pt-32 sm:pt-36 pb-10 sm:pb-12">
    {{-- Decorative gradient base first, photo above it — .marketing-page-hero-bg is opaque and must not cover the image --}}
    <div class="pointer-events-none absolute inset-0 z-0 marketing-page-hero-bg" aria-hidden="true"></div>
    <div
        class="pointer-events-none absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
        @if($heroBg)
            style="background-image: url('{{ asset($heroBg) }}')"
        @else
            style="background-image: url('{{ asset('assets/images/Image_ro410gro410gro41.png') }}')"
        @endif
        aria-hidden="true"
    ></div>
    <div
        class="pointer-events-none absolute inset-0 z-[1]"
        style="background: linear-gradient(180deg, rgba(15, 23, 42, 0.78) 0%, rgba(15, 23, 42, 0.72) 45%, rgba(15, 23, 42, 0.88) 100%);"
        aria-hidden="true"
    ></div>
    <div class="pointer-events-none absolute top-0 right-0 z-[1] w-[420px] h-[420px] bg-primary/20 blur-[120px] rounded-full" aria-hidden="true"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 z-[1] w-[320px] h-[320px] bg-accent/10 blur-[100px] rounded-full" aria-hidden="true"></div>

    <div class="relative z-10 max-w-marketing mx-auto px-5 sm:px-6">
        <nav class="text-sm text-slate-300" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-1.5">
                @foreach($crumbs as $i => $crumb)
                    <li class="inline-flex items-center gap-1.5">
                        @if($i > 0)
                            <span class="text-slate-500" aria-hidden="true">/</span>
                        @endif
                        @if(! empty($crumb['href']) && ! $loop->last)
                            <a href="{{ $crumb['href'] }}" class="hover:text-white transition-colors">{{ $crumb['label'] }}</a>
                        @else
                            <span class="{{ $loop->last ? 'text-white/90' : '' }}">{{ $crumb['label'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
</header>

{{-- Ecommerce buy box (white) --}}
<section class="bg-white text-slate-900">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 py-10 sm:py-14">
        <div
            class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start"
            x-data="{ active: 0, images: {{ Js::from($gallery) }} }"
        >
            {{-- Left: gallery --}}
            <div class="space-y-3">
                <div class="aspect-[4/3] sm:aspect-square rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
                    <img
                        :src="images[active].src"
                        :alt="images[active].alt"
                        class="w-full h-full object-cover"
                    >
                </div>
                @if($gallery->count() > 1)
                    <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                        @foreach($gallery as $i => $shot)
                            <button
                                type="button"
                                @click="active = {{ $i }}"
                                :class="active === {{ $i }} ? 'ring-2 ring-primary border-primary' : 'border-slate-200 hover:border-slate-300'"
                                class="aspect-square rounded-lg overflow-hidden border bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                            >
                                <img src="{{ $shot['src'] }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right: title, description, price, CTA --}}
            <div class="flex flex-col gap-4 sm:gap-5 lg:pt-1">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-primary mb-2">
                        {{ $product->product_type->label() }}
                        @if($product->productType)
                            <span class="text-slate-400 font-normal">· {{ $product->productType->name }}</span>
                        @endif
                    </p>
                    <h1 class="font-display text-xl sm:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight leading-tight">
                        {{ $product->title }}
                    </h1>
                </div>

                @if($subtitle)
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                        {{ $subtitle }}
                    </p>
                @endif

                <div class="border-t border-b border-slate-200 py-4">
                    <span class="text-[11px] font-medium uppercase tracking-widest text-slate-500 block">
                        {{ $variants->count() > 1 ? 'Pricing starts at' : 'Price' }}
                    </span>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-3xl font-display font-bold text-primary">
                            ₦{{ number_format($startPrice, 2) }}
                        </span>
                        @if($showPerMonthSuffix)
                            <span class="text-sm text-slate-500">/ mo</span>
                        @endif
                    </div>
                </div>

                @if($variants->count() > 1)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Choose a plan</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($variants as $variant)
                                @php
                                    $months = (int) ($variant->duration_months ?? 0);
                                    $isPopular = $variant->id === $popularVariantId;
                                    $isBestValue = $months > 0 && $months === $longestMonths;
                                @endphp
                                <div @class([
                                    'rounded-lg border px-3 py-2.5 text-left',
                                    'border-primary bg-primary/5' => $isPopular || $isBestValue,
                                    'border-slate-200 bg-slate-50' => ! ($isPopular || $isBestValue),
                                ])>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-semibold text-slate-900">{{ $variant->displayLabel() }}</span>
                                        @if($isBestValue)
                                            <span class="text-[9px] font-bold uppercase text-primary">Best</span>
                                        @elseif($isPopular)
                                            <span class="text-[9px] font-bold uppercase text-primary">Popular</span>
                                        @endif
                                    </div>
                                    <div class="text-sm font-bold text-primary mt-0.5">₦{{ number_format($variant->price, 2) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-3 pt-1">
                    <x-ui.button :href="$checkoutUrl" variant="primary" size="lg" class="!px-8 hover:!bg-accent">
                        {{ auth()->check() ? 'Buy Now' : 'Log in to buy' }}
                    </x-ui.button>
                    @include('partials.catalog.view-demo-modal', ['product' => $product])
                    @auth
                        <form method="POST" action="{{ route('favorites.toggle') }}">
                            @csrf
                            <input type="hidden" name="type" value="platform_product">
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            <x-ui.button type="submit" variant="secondary" size="lg" class="!bg-slate-100 !text-slate-800 !border-slate-200 hover:!bg-slate-200">
                                {{ ($isFavorited ?? false) ? 'Favorited' : 'Favorite' }}
                            </x-ui.button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Extra product information: description and pricing tiers only, each on its own grey card --}}
@php
    $showAbout = filled($product->description) && (! $subtitle || trim((string) $product->description) !== trim((string) $subtitle));
    $showTiers = $variants->count() > 1;
    $hasDetailSections = $showAbout || $showTiers;
    $cardClass = 'rounded-xl border border-slate-200 bg-slate-100 p-5 sm:p-6';
@endphp

@if($hasDetailSections)
<section class="bg-white text-slate-900 border-t border-slate-200">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 py-10 sm:py-14 space-y-5 sm:space-y-6">
        @if($showAbout)
            <div class="{{ $cardClass }}">
                <h2 class="font-display text-lg sm:text-xl font-semibold text-slate-900 mb-3">About this service</h2>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
            </div>
        @endif

        @if($showTiers)
            <div class="{{ $cardClass }}">
                <h2 class="font-display text-lg sm:text-xl font-semibold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="text-primary"><x-ui.icon name="wallet" class="w-5 h-5" /></span>
                    Pricing tiers
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($variants as $variant)
                        @php
                            $months = (int) ($variant->duration_months ?? 0);
                            $isPopular = $variant->id === $popularVariantId;
                            $isBestValue = $months > 0 && $months === $longestMonths;
                            $perMonth = $months > 0 ? ((float) $variant->price / $months) : null;
                            $savePct = null;
                            if ($monthlyVariant && $months > 1) {
                                $expected = (float) $monthlyVariant->price * $months;
                                if ($expected > 0 && (float) $variant->price < $expected) {
                                    $savePct = (int) round((1 - ((float) $variant->price / $expected)) * 100);
                                }
                            }
                            $tierBadge = $isBestValue ? 'Best Value' : ($isPopular && $savePct ? 'Save '.$savePct.'%' : ($isPopular ? 'Popular' : null));
                        @endphp
                        <div @class([
                            'rounded-xl border p-4 sm:p-5 bg-white',
                            'border-primary/40 ring-1 ring-primary/15' => $isPopular || $isBestValue,
                            'border-slate-200' => ! ($isPopular || $isBestValue),
                        ])>
                            <div class="flex justify-between items-start gap-2">
                                <div>
                                    <div class="text-xs font-medium uppercase tracking-wider text-primary mb-1">{{ $variant->name ?: 'Plan' }}</div>
                                    <div class="font-semibold text-slate-900">{{ $variant->displayLabel() }}</div>
                                </div>
                                @if($tierBadge)
                                    <span class="bg-primary/15 text-primary px-2 py-0.5 rounded text-[10px] font-bold uppercase whitespace-nowrap">{{ $tierBadge }}</span>
                                @endif
                            </div>
                            <div class="mt-4 text-lg font-bold text-slate-900">₦{{ number_format($variant->price, 2) }}</div>
                            @if($perMonth !== null)
                                <div class="text-xs text-slate-500">₦{{ number_format($perMonth, 2) }} / mo</div>
                            @elseif($months === 1)
                                <div class="text-xs text-slate-500">Billed monthly</div>
                            @else
                                <div class="text-xs text-slate-500">One-time</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>
@endif
@endsection
