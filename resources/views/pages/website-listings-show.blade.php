@extends('layouts.marketing')

@section('title', $product->title.' | Website')

@section('content')
@php
    $variants = $product->activeVariants->sortBy('price')->values();
    $defaultVariant = $variants->first();
    $heroUrl = media_url($product->heroMedia, $product->hero_image, 'large')
        ?? media_url($product->heroMedia, $product->hero_image, 'medium');
    $variantPayload = $variants->map(fn ($v) => [
        'id' => $v->id,
        'label' => $v->displayLabel(),
        'price' => (float) $v->price,
        'description' => (string) ($v->description ?? ''),
    ])->values();
@endphp
<section class="py-14 sm:py-20">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 space-y-10"
         x-data="{
            variants: @js($variantPayload),
            variantId: {{ (int) ($defaultVariant?->id ?? 0) }},
            get selected() {
                return this.variants.find(v => Number(v.id) === Number(this.variantId)) || this.variants[0] || null;
            },
            checkoutUrl() {
                const base = @js(route('dashboard.services.checkout', $product->slug));
                if (! this.selected) return base;
                return base + (base.includes('?') ? '&' : '?') + 'variant=' + this.selected.id;
            }
         }">
        <div class="space-y-6">
            <div class="rounded-2xl bg-slate-900/70 overflow-hidden">
                @if($heroUrl)
                    <img src="{{ $heroUrl }}" alt="{{ $product->title }}" class="block w-full h-auto max-h-[32rem] object-contain">
                @elseif($product->images->first())
                    <img src="{{ asset($product->images->first()->path) }}" alt="" class="block w-full h-auto max-h-[32rem] object-contain">
                @else
                    <div class="aspect-video flex items-center justify-center"><x-ui.icon name="inventory" class="w-16 h-16 text-accent" /></div>
                @endif
            </div>

            <div class="grid lg:grid-cols-2 gap-10">
                <div>
                    <h1 class="text-3xl font-bold font-display mb-3">{{ $product->title }}</h1>
                    @if(filled($product->description))
                        <p class="text-slate-400 mb-4 whitespace-pre-line">{{ $product->description }}</p>
                    @elseif(filled($product->short_description))
                        <p class="text-slate-400 mb-4">{{ $product->short_description }}</p>
                    @endif
                    <div class="flex flex-wrap gap-3 text-xs text-slate-300 mb-6">
                        @if($product->industry)<span class="px-2 py-1 rounded-lg bg-white/5">{{ $product->industry }}</span>@endif
                        @if($product->framework)<span class="px-2 py-1 rounded-lg bg-white/5">{{ $product->framework }}</span>@endif
                        @if($product->is_responsive)<span class="px-2 py-1 rounded-lg bg-white/5">Responsive</span>@endif
                        @if($product->is_seo_ready)<span class="px-2 py-1 rounded-lg bg-white/5">SEO ready</span>@endif
                        @if($product->support_period)<span class="px-2 py-1 rounded-lg bg-white/5">Support {{ $product->support_period }}</span>@endif
                    </div>

                    @if($variants->isNotEmpty())
                        <div class="space-y-3 mb-6">
                            <p class="text-sm font-semibold text-slate-200">Choose a plan</p>
                            @foreach($variants as $variant)
                                <label
                                    class="flex cursor-pointer flex-col gap-1 rounded-xl border px-4 py-3 transition-colors"
                                    :class="Number(variantId) === {{ (int) $variant->id }} ? 'border-primary bg-primary/10' : 'border-white/10 hover:border-white/25'"
                                >
                                    <span class="flex items-center justify-between gap-3">
                                        <span class="flex items-center gap-3">
                                            <input type="radio" name="preview_variant_id" value="{{ $variant->id }}" class="accent-primary" x-model.number="variantId" @checked((int) $defaultVariant?->id === (int) $variant->id)>
                                            <span class="text-sm font-medium">{{ $variant->displayLabel() }}</span>
                                        </span>
                                        <span class="font-semibold">₦{{ number_format((float) $variant->price, 0) }}</span>
                                    </span>
                                    @if(filled($variant->description))
                                        <span class="pl-7 text-xs leading-relaxed text-slate-400" x-show="Number(variantId) === {{ (int) $variant->id }}">{{ $variant->description }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <p class="text-sm text-slate-400">Selected plan</p>
                    <p class="text-xl font-semibold" x-text="selected ? selected.label : '—'"></p>
                    <p class="text-3xl font-bold">
                        <span x-text="selected ? ('₦' + Number(selected.price).toLocaleString('en-NG')) : ('From ₦{{ number_format($product->displayPrice(), 0) }}')"></span>
                    </p>
                    <p class="text-sm text-slate-400">From ₦{{ number_format($product->displayPrice(), 0) }}</p>

                    <div class="flex flex-wrap gap-3 pt-2">
                        @php
                            $demoIntegration = $product->siteIntegration;
                            $canDemoUser = $demoIntegration?->isActive()
                                && $demoIntegration->hasCapability(\App\Models\SiteIntegration::CAP_DEMO_USER_LOGIN)
                                && filled($demoIntegration->demo_user_email);
                            $canDemoAdmin = $demoIntegration?->isActive()
                                && $demoIntegration->hasCapability(\App\Models\SiteIntegration::CAP_DEMO_ADMIN_LOGIN)
                                && filled($demoIntegration->demo_admin_email);
                            $showDemo = $canDemoUser || $canDemoAdmin;
                        @endphp
                        @if ($showDemo)
                            <a href="{{ route('login') }}" class="px-5 py-3 rounded-xl border border-white/15 font-bold hover:bg-white/5">Log in to view demo</a>
                        @endif
                        <a href="#" x-bind:href="checkoutUrl()" class="px-5 py-3 rounded-xl bg-primary hover:bg-accent font-bold">Log in to buy</a>
                    </div>
                </div>
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
