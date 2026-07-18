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
<a href="{{ $href }}" class="group flex flex-col h-full overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-900 shadow-sm hover:shadow-md transition-shadow">
    <div class="relative aspect-[2/1] bg-slate-800 overflow-hidden shrink-0">
        @if($product->hero_image)
            <img src="{{ asset($product->hero_image) }}" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/50 via-slate-800 to-slate-900">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15 border border-white/20 text-white font-bold text-sm font-display">
                    {{ $initials }}
                </span>
            </div>
        @endif
        <span class="absolute top-2 left-2 rounded bg-slate-950/75 border border-white/10 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white">
            {{ $product->product_type->label() }}
        </span>
    </div>
    <div class="flex flex-1 flex-col gap-2 p-4 sm:p-5 text-left">
        <h3 class="font-bold text-sm sm:text-base text-slate-900 leading-snug line-clamp-2">{{ $product->title }}</h3>
        <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-1">
            <span class="font-bold text-primary text-sm sm:text-base">₦{{ number_format($product->displayPrice(), 0) }}</span>
            <span class="inline-flex items-center justify-center rounded-lg bg-primary px-3 py-1.5 text-xs sm:text-sm font-semibold text-white group-hover:bg-accent transition-colors whitespace-nowrap">
                Buy now
            </span>
        </div>
    </div>
</a>
