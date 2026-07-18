@php
    $href = match ($product->product_type->defaultRoute()) {
        'templates' => route('templates.show', $product->slug),
        'website-listings' => route('website-listings.show', $product->slug),
        default => route('services.show', [
            'type' => $product->product_type->value,
            'productSlug' => $product->slug,
        ]),
    };
    $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $product->title) ?: 'P', 0, 2));
@endphp
<a href="{{ $href }}" class="group glassmorphism rounded-2xl overflow-hidden hover:border-accent/50 transition-all flex flex-col h-full">
    <div class="relative aspect-[2/1] bg-slate-900 overflow-hidden">
        @if($product->hero_image)
            <img src="{{ asset($product->hero_image) }}" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/35 via-slate-900 to-slate-950">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 border border-white/15 text-white font-bold text-lg font-display">
                    {{ $initials }}
                </span>
            </div>
        @endif
        <span class="absolute top-3 left-3 rounded-md bg-slate-950/70 border border-white/10 px-2 py-0.5 text-[10px] uppercase tracking-wide text-accent">
            {{ $product->product_type->label() }}
        </span>
    </div>
    <div class="p-4 sm:p-5 flex flex-col flex-1 text-left">
        <h3 class="font-bold text-base sm:text-lg text-white leading-snug line-clamp-2">{{ $product->title }}</h3>
        @if($product->short_description)
            <p class="mt-1.5 text-sm text-slate-400 line-clamp-2 flex-1">{{ $product->short_description }}</p>
        @else
            <div class="flex-1"></div>
        @endif
        <div class="mt-4 flex items-center justify-between gap-3">
            <span class="font-bold text-white">₦{{ number_format($product->displayPrice(), 0) }}</span>
            <span class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-900 group-hover:bg-accent group-hover:text-white transition-colors">
                View
                <span aria-hidden="true">→</span>
            </span>
        </div>
    </div>
</a>
