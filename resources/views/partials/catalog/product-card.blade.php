@php
    $browse = app(\App\Modules\Catalog\Services\CatalogBrowseService::class);
    // Listing cards open the public product page from both the card and the CTA.
    $href = match ($product->product_type?->defaultRoute()) {
        'website-listings' => route('website-listings.show', $product->slug),
        default => $browse->productUrl($product),
    };
    $typeLabel = $product->productType?->name
        ?? $product->product_type?->label()
        ?? 'Service';
    $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $product->title) ?: 'P', 0, 2));
    $heroUrl = media_url($product->heroMedia ?? null, $product->hero_image, 'medium');
@endphp
<div class="group flex flex-col h-full overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-900 shadow-sm hover:shadow-md transition-shadow">
    <a href="{{ $href }}" class="relative aspect-[2/1] bg-slate-100 overflow-hidden shrink-0 block">
        @if($heroUrl)
            <img src="{{ $heroUrl }}" alt="" class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-[1.02]">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/50 via-slate-800 to-slate-900">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15 border border-white/20 text-white font-bold text-sm font-display">
                    {{ $initials }}
                </span>
            </div>
        @endif
        <span class="absolute top-2 left-2 rounded bg-slate-950/75 border border-white/10 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white">
            {{ $typeLabel }}
        </span>
    </a>
    <div class="flex flex-1 flex-col gap-2 p-4 sm:p-5 text-left">
        <a href="{{ $href }}" class="font-bold text-sm sm:text-base text-slate-900 leading-snug line-clamp-2 hover:text-primary">{{ $product->title }}</a>
        @if(filled($product->short_description))
            <p class="text-xs sm:text-sm text-slate-500 line-clamp-2">{{ $product->short_description }}</p>
        @endif
        <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-1">
            <span class="font-bold text-primary text-sm sm:text-base">From ₦{{ number_format($product->displayPrice(), 0) }}</span>
            <a href="{{ $href }}" class="inline-flex items-center justify-center rounded-lg bg-primary px-3 py-1.5 text-xs sm:text-sm font-semibold text-white hover:bg-accent transition-colors whitespace-nowrap">
                View now
            </a>
        </div>
    </div>
</div>
