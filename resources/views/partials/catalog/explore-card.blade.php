@php
    /** @var array $card */
    $href = $card['href'] ?? '#';
    $label = $card['label'] ?? '';
    $desc = $card['short_description'] ?? '';
    $image = $card['card_image'] ?? null;
    $icon = $card['icon'] ?? 'grid';
    $meta = $card['meta'] ?? null;
    $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $label) ?: 'S', 0, 2));
    $ctaLabel = $card['cta'] ?? 'Explore';

    $imageSrc = null;
    if (is_string($image) && $image !== '') {
        $trimmed = trim($image);
        if (preg_match('#^(https?:)?//#i', $trimmed) || str_starts_with($trimmed, '/')) {
            $imageSrc = $trimmed;
        } else {
            $imageSrc = asset(ltrim(str_replace('\\', '/', $trimmed), '/'));
        }
    }
@endphp
<div class="group flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-900 shadow-sm transition-shadow hover:shadow-md">
    <a href="{{ $href }}" class="relative aspect-[2/1] shrink-0 overflow-hidden bg-slate-800 focus-ring" tabindex="-1" aria-hidden="true">
        @if($imageSrc)
            <img src="{{ $imageSrc }}" alt="" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/50 via-slate-800 to-slate-900">
                <span class="flex h-12 w-12 items-center justify-center rounded-full border border-white/20 bg-white/15 font-display text-sm font-bold text-white" aria-hidden="true">
                    {{ $initials }}
                </span>
            </div>
            <div class="absolute bottom-2 right-2 opacity-50">
                <x-ui.icon :name="$icon" class="h-5 w-5 text-white" />
            </div>
        @endif
    </a>
    <div class="flex flex-1 flex-col gap-2 p-4 text-left sm:p-5">
        <h3 class="line-clamp-2 font-display text-sm font-bold leading-snug text-slate-900 sm:text-base">
            <a href="{{ $href }}" class="hover:text-primary focus-ring rounded-sm">{{ $label }}</a>
        </h3>
        @if($desc)
            <p class="line-clamp-2 text-xs text-slate-500 sm:text-sm">{{ $desc }}</p>
        @endif
        @if($meta)
            <p class="text-xs text-slate-400">{{ $meta }}</p>
        @endif
        <div class="flex-1"></div>
        <div class="mt-1">
            <x-ui.button :href="$href" size="xs" variant="primary">
                {{ $ctaLabel }}
            </x-ui.button>
        </div>
    </div>
</div>
