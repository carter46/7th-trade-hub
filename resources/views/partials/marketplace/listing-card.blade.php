@php
    $href = route('marketplace.show', $listing->slug);
    $vendor = $listing->user?->name ?? 'Seller';
    $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $vendor) ?: 'V', 0, 2));
@endphp
<a href="{{ $href }}" class="group glassmorphism rounded-2xl overflow-hidden hover:border-accent/50 transition-all flex flex-col h-full">
    <div class="relative aspect-[2/1] bg-slate-900 overflow-hidden">
        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/30 via-slate-900 to-slate-950">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-white/10 border border-white/15 text-white font-bold text-lg font-display">
                {{ $initials }}
            </span>
        </div>
        @if($listing->featured)
            <span class="absolute top-3 left-3 rounded-md bg-warning/90 text-slate-900 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">
                Featured
            </span>
        @endif
    </div>
    <div class="p-4 sm:p-5 flex flex-col flex-1 text-left">
        <h3 class="font-bold text-base sm:text-lg text-white leading-snug line-clamp-2">{{ $listing->title }}</h3>
        <p class="mt-1 text-xs text-slate-500">
            @if($listing->listingCategory)
                {{ $listing->listingCategory->name }} ·
            @endif
            {{ $vendor }}
        </p>
        @if($listing->description)
            <p class="mt-1.5 text-sm text-slate-400 line-clamp-2 flex-1">{{ Str::limit($listing->description, 100) }}</p>
        @else
            <div class="flex-1"></div>
        @endif
        <div class="mt-4 flex items-center justify-between gap-3">
            <span class="font-bold text-accent">₦{{ number_format($listing->price, 0) }}</span>
            <span class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-900 group-hover:bg-accent group-hover:text-white transition-colors">
                View details
                <span aria-hidden="true">→</span>
            </span>
        </div>
    </div>
</a>
