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
<a href="{{ $href }}" class="group glassmorphism rounded-2xl overflow-hidden hover:border-accent/50 transition-all flex flex-col h-full">
    <div class="relative aspect-[2/1] bg-slate-900 overflow-hidden">
        @if($image)
            <img src="{{ asset($image) }}" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/40 via-slate-900 to-slate-950">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 border border-white/15 text-white font-bold text-lg font-display" aria-hidden="true">
                    {{ $initials }}
                </span>
            </div>
            <div class="absolute bottom-3 right-3 opacity-40">
                <x-ui.icon :name="$icon" class="w-6 h-6 text-accent" />
            </div>
        @endif
    </div>
    <div class="p-4 sm:p-5 flex flex-col flex-1 text-left">
        <h3 class="font-bold text-base sm:text-lg text-white leading-snug">{{ $label }}</h3>
        @if($desc)
            <p class="mt-1.5 text-sm text-slate-400 line-clamp-2 flex-1">{{ $desc }}</p>
        @else
            <div class="flex-1"></div>
        @endif
        <span class="mt-4 inline-flex w-fit items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-slate-900 group-hover:bg-accent group-hover:text-white transition-colors">
            {{ $ctaLabel }}
            <span aria-hidden="true">→</span>
        </span>
    </div>
</a>
