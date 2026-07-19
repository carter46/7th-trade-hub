@php
    /** @var array $card */
    $href = $card['href'] ?? '#';
    $label = $card['label'] ?? '';
    $desc = $card['short_description'] ?? '';
    $image = $card['card_image'] ?? null;
    $icon = $card['icon'] ?? 'grid';
    $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $label) ?: 'S', 0, 2));
    $ctaLabel = $card['cta'] ?? 'Explore';
@endphp
<a href="{{ $href }}" class="group flex flex-col h-full overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-900 shadow-sm hover:shadow-md transition-shadow">
    <div class="relative aspect-[2/1] bg-slate-800 overflow-hidden shrink-0">
        @if($image)
            <img src="{{ asset($image) }}" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/50 via-slate-800 to-slate-900">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15 border border-white/20 text-white font-bold text-sm font-display" aria-hidden="true">
                    {{ $initials }}
                </span>
            </div>
            <div class="absolute bottom-2 right-2 opacity-50">
                <x-ui.icon :name="$icon" class="w-5 h-5 text-white" />
            </div>
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-2 p-4 sm:p-5 text-left">
        <h3 class="font-bold text-sm sm:text-base text-slate-900 leading-snug line-clamp-2">{{ $label }}</h3>
        @if($desc)
            <p class="text-xs sm:text-sm text-slate-500 line-clamp-2 flex-1">{{ $desc }}</p>
        @else
            <div class="flex-1"></div>
        @endif
        <span class="mt-1 inline-flex w-fit items-center justify-center rounded-lg bg-primary px-3 py-1.5 text-xs sm:text-sm font-semibold text-white group-hover:bg-accent transition-colors">
            {{ $ctaLabel }}
        </span>
    </div>
</a>
