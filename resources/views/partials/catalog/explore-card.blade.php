@php
    /** @var array $card */
    $count = (int) ($card['count'] ?? 0);
    $from = $card['from_price'] ?? null;
    $href = $card['href'] ?? '#';
    $label = $card['label'] ?? '';
    $desc = $card['short_description'] ?? '';
    $image = $card['card_image'] ?? null;
    $icon = $card['icon'] ?? 'grid';
@endphp
<a href="{{ $href }}" class="glassmorphism rounded-2xl overflow-hidden hover:border-accent/40 transition-all flex flex-col h-full group">
    <div class="aspect-[16/9] bg-slate-900/70 overflow-hidden flex items-center justify-center">
        @if($image)
            <img src="{{ asset($image) }}" alt="" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300">
        @else
            <x-ui.icon :name="$icon" class="w-12 h-12 text-accent" />
        @endif
    </div>
    <div class="p-5 flex flex-col flex-1">
        <h3 class="font-bold text-lg mb-1.5">{{ $label }}</h3>
        @if($desc)
            <p class="text-sm text-slate-400 mb-4 flex-1 line-clamp-2">{{ $desc }}</p>
        @else
            <div class="flex-1 mb-4"></div>
        @endif
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm mb-4">
            <span class="text-slate-300">{{ $count }} {{ \Illuminate\Support\Str::plural('Service', $count) }}</span>
            @if($from !== null)
                <span class="font-semibold text-white">From ₦{{ number_format($from, 0) }}</span>
            @else
                <span class="text-slate-500">—</span>
            @endif
        </div>
        <span class="inline-flex items-center justify-center w-full px-4 py-2.5 rounded-xl bg-primary/90 group-hover:bg-accent font-semibold text-sm transition-colors">Explore</span>
    </div>
</a>
