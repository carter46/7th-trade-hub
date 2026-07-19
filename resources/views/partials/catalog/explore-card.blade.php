@php
    /** @var array $card */
    $href = $card['href'] ?? '#';
    $label = $card['label'] ?? '';
    $desc = $card['short_description'] ?? '';
    $image = $card['card_image'] ?? null;
    $icon = $card['icon'] ?? 'grid';
    $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $label) ?: 'S', 0, 2));
    $ctaLabel = $card['cta'] ?? 'Explore Services';
@endphp
<a href="{{ $href }}" class="group flex flex-col h-full bg-white p-6 sm:p-8 rounded-xl transition-all hover:shadow-lg">
    <div class="w-full h-40 rounded-lg overflow-hidden mb-4 bg-slate-800 shrink-0">
        @if($image)
            <img src="{{ asset($image) }}" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary/60 via-slate-800 to-slate-900">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 border border-white/20 text-white font-bold text-lg font-display" aria-hidden="true">
                    {{ $initials }}
                </span>
            </div>
        @endif
    </div>

    <h3 class="font-display text-xl sm:text-2xl font-semibold text-surface mb-2 leading-snug">{{ $label }}</h3>

    @if($desc)
        <p class="text-sm sm:text-base text-slate-500 leading-relaxed mb-6 flex-1 line-clamp-3">{{ $desc }}</p>
    @else
        <div class="flex-1 mb-6"></div>
    @endif

    <span class="inline-flex items-center gap-2 text-primary text-xs sm:text-sm font-bold uppercase tracking-widest group-hover:gap-3 group-hover:text-accent transition-all">
        {{ $ctaLabel }}
        <x-ui.icon name="arrow-right" class="w-4 h-4" />
    </span>
</a>
