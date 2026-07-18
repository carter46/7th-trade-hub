@php
    $href = route('marketplace.show', $listing->slug);
    $vendor = $listing->user?->name ?? 'Seller';
    $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $vendor) ?: 'V', 0, 2));
@endphp
<article class="flex overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-900 shadow-sm hover:shadow-md transition-shadow">
    <a href="{{ $href }}" class="relative w-24 sm:w-32 md:w-36 shrink-0 min-h-[6.5rem] sm:min-h-[7.5rem] bg-slate-800" aria-label="{{ $listing->title }}">
        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/50 via-slate-800 to-slate-900">
            <span class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-white/15 border border-white/20 text-white font-bold text-sm font-display">
                {{ $initials }}
            </span>
        </div>
        @if($listing->featured)
            <span class="absolute top-2 left-2 rounded bg-warning text-slate-900 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide">
                Featured
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col justify-center gap-2 sm:gap-3 p-4 sm:p-5 min-w-0 bg-white text-left">
        <h2 class="text-sm sm:text-base md:text-lg font-bold text-slate-900 leading-snug line-clamp-2">
            <a href="{{ $href }}" class="hover:text-primary transition-colors">{{ $listing->title }}</a>
        </h2>
        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[11px] sm:text-xs text-slate-500">
            @if($listing->listingCategory)
                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-slate-600">{{ $listing->listingCategory->name }}</span>
            @endif
            <span>by {{ $vendor }}</span>
            <span class="font-bold text-primary text-xs sm:text-sm">₦{{ number_format($listing->price, 0) }}</span>
        </div>
        <div>
            <a href="{{ $href }}"
               class="inline-flex items-center justify-center rounded-lg bg-primary px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-semibold text-white hover:bg-accent transition-colors whitespace-nowrap">
                View details
            </a>
        </div>
    </div>
</article>
