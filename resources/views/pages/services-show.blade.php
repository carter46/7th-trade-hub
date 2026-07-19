@extends('layouts.marketing')

@section('title', $product->title.' | 7th Trade Hub')

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
        // Skip redundant crumb when group has a single type and labels match.
        $typeLabel = $typeContent['label'] ?? $product->product_type->label();
        $lastGroup = $groupContent['label'] ?? null;
        if ($typeLabel !== $lastGroup) {
            $crumbs[] = ['label' => $typeLabel, 'href' => route('services.segment', $typeKey)];
        }
    }
    $crumbs[] = ['label' => $product->title];

    $heroImage = $product->hero_image
        ? asset($product->hero_image)
        : asset('assets/images/Image_ro410gro410gro41.png');

    $subtitle = $product->short_description ?: $product->description;
    $startPrice = $product->displayPrice();
    $checkoutUrl = route('checkout.platform.show', $product->slug);

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

    $featureIcons = ['rocket', 'wallet', 'support', 'lock', 'verified', 'grid'];
    $includeIcons = ['check', 'listings', 'support'];

    $features = collect($product->features ?? [])->map(function ($item) {
        if (is_array($item)) {
            return [
                'title' => $item['title'] ?? $item['label'] ?? '',
                'blurb' => $item['blurb'] ?? $item['description'] ?? $item['a'] ?? null,
            ];
        }

        return ['title' => (string) $item, 'blurb' => null];
    })->filter(fn ($f) => $f['title'] !== '');

    $requirements = collect($product->requirements ?? [])->map(fn ($item) => is_array($item)
        ? (string) ($item['title'] ?? $item['label'] ?? $item['text'] ?? '')
        : (string) $item
    )->filter();

    $included = collect($product->whats_included ?? [])->map(fn ($item) => is_array($item)
        ? (string) ($item['title'] ?? $item['label'] ?? $item['text'] ?? '')
        : (string) $item
    )->filter();

    $faqs = collect($product->faqs ?? [])->filter(fn ($f) => is_array($f) && ! empty($f['q']));

    $supportHref = auth()->check()
        ? route('dashboard.support.index')
        : route('login');
@endphp

{{-- Hero --}}
<section class="relative min-h-[55vh] flex flex-col justify-center items-center px-5 sm:px-6 overflow-hidden">
    <div class="absolute inset-0 z-0 opacity-40" aria-hidden="true">
        <img alt="" class="w-full h-full object-cover" src="{{ $heroImage }}">
        <div class="absolute inset-0 bg-gradient-to-b from-surface/40 via-surface/70 to-surface"></div>
    </div>
    <div class="pointer-events-none absolute inset-0 z-[1] bg-[radial-gradient(circle_at_center,rgba(11,106,57,0.12)_0%,transparent_70%)]" aria-hidden="true"></div>

    <div class="relative z-10 max-w-marketing mx-auto w-full text-center py-14 sm:py-20">
        <nav class="flex flex-wrap justify-center items-center gap-1.5 mb-4 text-xs text-text-secondary" aria-label="Breadcrumb">
            @foreach($crumbs as $i => $crumb)
                @if($i > 0)
                    <x-ui.icon name="chevron-right" class="w-3.5 h-3.5 text-text-muted shrink-0" />
                @endif
                @if(! empty($crumb['href']) && ! $loop->last)
                    <a href="{{ $crumb['href'] }}" class="hover:text-accent transition-colors">{{ $crumb['label'] }}</a>
                @else
                    <span class="{{ $loop->last ? 'text-accent font-medium' : '' }}">{{ $crumb['label'] }}</span>
                @endif
            @endforeach
        </nav>

        <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white mb-4 leading-tight">
            {{ $product->title }}
        </h1>

        @if($subtitle)
            <p class="max-w-2xl mx-auto text-base sm:text-lg text-text-secondary leading-relaxed">
                {{ $subtitle }}
            </p>
        @endif

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6">
            <div class="text-center">
                <span class="text-[11px] font-medium uppercase tracking-widest text-text-secondary block">Pricing starts at</span>
                <div class="text-2xl sm:text-3xl font-display font-semibold text-accent mt-1">
                    ₦{{ number_format($startPrice, 2) }}
                    @if($showPerMonthSuffix)
                        <span class="text-sm font-normal text-text-secondary">/ mo</span>
                    @endif
                </div>
            </div>
            <x-ui.button :href="$checkoutUrl" variant="primary" size="lg" class="!px-10 !h-12 !text-base shadow-lg shadow-primary/20 hover:!bg-accent">
                Buy Now
            </x-ui.button>
            @auth
                <form method="POST" action="{{ route('favorites.toggle') }}">
                    @csrf
                    <input type="hidden" name="type" value="platform_product">
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <x-ui.button type="submit" variant="secondary" size="lg">
                        {{ ($isFavorited ?? false) ? 'Favorited' : 'Favorite' }}
                    </x-ui.button>
                </form>
            @endauth
        </div>
    </div>
</section>

{{-- Specs grid --}}
<section class="max-w-marketing mx-auto px-5 sm:px-6 py-14 sm:py-16">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-8">
        {{-- Pricing tiers --}}
        <div class="md:col-span-8 flex flex-col gap-4">
            <h2 class="font-display text-xl sm:text-2xl font-semibold text-white mb-1 flex items-center gap-3">
                <span class="text-accent"><x-ui.icon name="wallet" class="w-6 h-6" /></span>
                Pricing Tiers
            </h2>

            @if($variants->isEmpty())
                <div class="glassmorphism p-6 rounded-xl">
                    <div class="text-lg font-bold text-white">₦{{ number_format($product->base_price, 2) }}</div>
                    <span class="text-xs text-text-secondary">Base price</span>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    @foreach($variants as $variant)
                        @php
                            $months = (int) ($variant->duration_months ?? 0);
                            $isPopular = $variant->id === $popularVariantId && $variants->count() > 1;
                            $isBestValue = $months > 0 && $months === $longestMonths && $variants->count() > 1;
                            $perMonth = $months > 0 ? ((float) $variant->price / $months) : null;
                            $savePct = null;
                            if ($monthlyVariant && $months > 1) {
                                $expected = (float) $monthlyVariant->price * $months;
                                if ($expected > 0 && (float) $variant->price < $expected) {
                                    $savePct = (int) round((1 - ((float) $variant->price / $expected)) * 100);
                                }
                            }
                            $tierBadge = $isBestValue ? 'Best Value' : ($isPopular && $savePct ? 'Save '.$savePct.'%' : ($isPopular ? 'Popular' : null));
                            $featured = $isPopular || $isBestValue;
                        @endphp
                        <div @class([
                            'glassmorphism p-6 rounded-xl flex flex-col justify-between transition-all hover:border-accent/40',
                            'border-accent/50 ring-1 ring-accent/20 shadow-lg shadow-primary/5' => $featured,
                        ])>
                            <div class="flex justify-between items-start gap-3">
                                <div>
                                    <h3 class="text-xs font-medium uppercase tracking-wider text-accent mb-2">
                                        {{ $variant->name ?: 'Plan' }}
                                    </h3>
                                    <div class="text-xl font-display font-semibold text-white">
                                        {{ $variant->displayLabel() }}
                                    </div>
                                </div>
                                @if($tierBadge)
                                    <span class="bg-primary/20 text-accent px-2 py-1 rounded text-[10px] font-bold uppercase whitespace-nowrap">
                                        {{ $tierBadge }}
                                    </span>
                                @endif
                            </div>
                            <div class="mt-6">
                                <div class="text-lg font-bold text-white">₦{{ number_format($variant->price, 2) }}</div>
                                @if($perMonth !== null)
                                    <span class="text-xs text-text-secondary">₦{{ number_format($perMonth, 2) }} / mo</span>
                                @elseif($months === 1)
                                    <span class="text-xs text-text-secondary">Billed monthly</span>
                                @else
                                    <span class="text-xs text-text-secondary">One-time</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Requirements --}}
        <div class="md:col-span-4 glassmorphism p-6 sm:p-8 rounded-xl flex flex-col gap-6 h-full">
            <h2 class="font-display text-lg sm:text-xl font-semibold text-white flex items-center gap-3">
                <span class="text-warning"><x-ui.icon name="warning" class="w-5 h-5" /></span>
                Requirements
            </h2>
            @if($requirements->isEmpty())
                <p class="text-sm text-text-secondary">No special requirements listed for this product.</p>
            @else
                <ul class="space-y-4">
                    @foreach($requirements as $req)
                        <li class="flex gap-3 items-start">
                            <span class="text-accent mt-0.5 shrink-0"><x-ui.icon name="check" class="w-5 h-5" /></span>
                            <span class="text-sm sm:text-base text-text-primary">{{ $req }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <div class="mt-auto pt-6 border-t border-border-subtle">
                <p class="text-xs text-text-secondary italic">
                    Complete KYC when prompted so purchases and withdrawals process without delay.
                </p>
            </div>
        </div>

        {{-- Features --}}
        <div class="md:col-span-6 glassmorphism p-6 sm:p-8 rounded-xl">
            <h2 class="font-display text-lg sm:text-xl font-semibold text-white mb-4 flex items-center gap-3">
                <span class="text-accent"><x-ui.icon name="rocket" class="w-5 h-5" /></span>
                Features
            </h2>
            @if($features->isEmpty())
                <p class="text-sm text-text-secondary">Feature details coming soon.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($features as $i => $feature)
                        <div class="bg-muted/40 p-4 rounded-lg flex items-start gap-3">
                            <span class="text-accent p-2 bg-primary/15 rounded-lg shrink-0">
                                <x-ui.icon :name="$featureIcons[$i % count($featureIcons)]" class="w-5 h-5" />
                            </span>
                            <div>
                                <div class="font-bold text-sm text-white">{{ $feature['title'] }}</div>
                                @if(! empty($feature['blurb']))
                                    <div class="text-xs text-text-secondary mt-0.5">{{ $feature['blurb'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- What's included --}}
        <div class="md:col-span-6 glassmorphism p-6 sm:p-8 rounded-xl">
            <h2 class="font-display text-lg sm:text-xl font-semibold text-white mb-4 flex items-center gap-3">
                <span class="text-accent"><x-ui.icon name="inventory" class="w-5 h-5" /></span>
                What's Included
            </h2>
            @if($included->isEmpty())
                <p class="text-sm text-text-secondary">Inclusions will be listed here.</p>
            @else
                <div class="space-y-3">
                    @foreach($included as $i => $item)
                        <div class="flex items-center gap-3 p-3 border-l-2 border-accent bg-primary/5">
                            <span class="text-accent shrink-0">
                                <x-ui.icon :name="$includeIcons[$i % count($includeIcons)]" class="w-5 h-5" />
                            </span>
                            <span class="font-bold text-sm text-white">{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if($product->description && $product->short_description && $product->description !== $product->short_description)
        <div class="mt-8 glassmorphism p-6 sm:p-8 rounded-xl">
            <h2 class="font-display text-lg sm:text-xl font-semibold text-white mb-3">About this service</h2>
            <p class="text-sm sm:text-base text-text-secondary leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
        </div>
    @endif
</section>

{{-- FAQs --}}
<section class="bg-elevated/40 border-t border-border-subtle py-14 sm:py-16">
    <div class="max-w-3xl mx-auto px-5 sm:px-6">
        <div class="text-center mb-8">
            <h2 class="font-display text-xl sm:text-2xl font-semibold text-white mb-2">Frequently Asked Questions</h2>
            <p class="text-text-secondary text-sm sm:text-base">
                Everything you need to know about {{ $product->title }}.
            </p>
        </div>

        <div class="space-y-4">
            @forelse($faqs as $faq)
                <details class="group glassmorphism rounded-xl overflow-hidden [&_summary::-webkit-details-marker]:hidden">
                    <summary class="flex justify-between items-center gap-4 p-5 sm:p-6 cursor-pointer hover:bg-white/5 transition-colors">
                        <h3 class="font-bold text-base text-white text-left">{{ $faq['q'] }}</h3>
                        <span class="text-text-secondary transition-transform group-open:rotate-180 shrink-0">
                            <x-ui.icon name="chevron-down" class="w-5 h-5" />
                        </span>
                    </summary>
                    <div class="px-5 sm:px-6 pb-5 sm:pb-6 text-sm text-text-secondary leading-relaxed">
                        {{ $faq['a'] ?? '' }}
                    </div>
                </details>
            @empty
                <p class="text-center text-sm text-text-secondary">No FAQs published for this product yet.</p>
            @endforelse

            <div class="mt-8 p-6 rounded-xl border border-dashed border-border-default text-center">
                <p class="text-sm text-text-primary mb-4">
                    {{ $product->support_text ?: 'Still have questions?' }}
                </p>
                <a href="{{ $supportHref }}" class="inline-flex items-center gap-2 text-accent font-bold hover:underline text-sm">
                    <x-ui.icon name="support" class="w-5 h-5" />
                    Open a support ticket from your dashboard
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
