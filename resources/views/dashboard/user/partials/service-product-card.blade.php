@php
    $heroUrl = media_url($product->heroMedia ?? null, $product->hero_image ?? null, 'medium');
    $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/', '', $product->title) ?: 'P', 0, 2));
    $href = route('dashboard.services.product', $product->slug);
@endphp
<x-dashboard.card :padding="false" class="overflow-hidden h-full">
    <a href="{{ $href }}" class="relative block aspect-[2/1] bg-muted">
        @if($heroUrl)
            <img src="{{ $heroUrl }}" alt="" class="h-full w-full object-contain">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary/40 via-muted to-elevated">
                <span class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/15 text-xs font-bold text-white" aria-hidden="true">
                    {{ $initials }}
                </span>
            </div>
        @endif
    </a>
    <div class="space-y-2 p-4">
        <a href="{{ $href }}" class="block font-semibold text-text-primary line-clamp-2 hover:text-primary">{{ $product->title }}</a>
        @if(filled($product->short_description))
            <p class="text-sm text-text-secondary line-clamp-2">{{ $product->short_description }}</p>
        @endif
        <div class="flex items-center justify-between gap-2 pt-1">
            <span class="font-semibold text-primary">From ₦{{ number_format($product->displayPrice(), 0) }}</span>
            <x-dashboard.button :href="$href" size="xs">View</x-dashboard.button>
        </div>
    </div>
</x-dashboard.card>
