@php
    $href = route('marketplace.show', $listing->slug);
    $vendor = $listing->user?->name ?? 'Seller';
    $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $vendor) ?: 'V', 0, 2));
@endphp
<article class="flex overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-900 shadow-sm hover:shadow-md transition-shadow">
    <a href="{{ $href }}" class="relative w-28 sm:w-36 md:w-40 shrink-0 min-h-[7.5rem] bg-slate-800" aria-label="{{ $listing->title }}">
        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/50 via-slate-800 to-slate-900">
            <span class="flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-white/15 border border-white/20 text-white font-bold text-sm font-display">
                {{ $initials }}
            </span>
        </div>
        @if($listing->featured)
            <span class="absolute top-2.5 left-2.5 rounded bg-warning text-slate-900 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide">
                Featured
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col sm:flex-row sm:items-center gap-4 p-5 sm:p-6 min-w-0 bg-white">
        <div class="flex-1 min-w-0 text-left pr-1">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                <a href="{{ $href }}" class="hover:text-primary transition-colors">{{ $listing->title }}</a>
            </h2>
            @if($listing->description)
                <p class="mt-2 text-sm text-slate-600 leading-relaxed line-clamp-2">{{ Str::limit($listing->description, 140) }}</p>
            @endif
            <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-slate-500">
                @if($listing->listingCategory)
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-slate-600">{{ $listing->listingCategory->name }}</span>
                @endif
                <span>by {{ $vendor }}</span>
                <span class="font-bold text-primary text-sm">₦{{ number_format($listing->price, 0) }}</span>
            </div>
        </div>
        <div class="shrink-0 pt-1 sm:pt-0 sm:pl-2">
            <a href="{{ $href }}"
               class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-accent transition-colors whitespace-nowrap">
                View details
            </a>
        </div>
    </div>
</article>
