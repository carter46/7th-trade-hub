@php
    $href = match ($product->product_type->defaultRoute()) {
        'templates' => route('templates.show', $product->slug),
        'website-listings' => route('website-listings.show', $product->slug),
        default => route('services.show', $product->slug),
    };
@endphp
<a href="{{ $href }}" class="glassmorphism rounded-2xl p-5 hover:border-accent/40 transition-all flex flex-col h-full">
    <div class="aspect-video rounded-xl bg-slate-900/70 mb-4 overflow-hidden flex items-center justify-center">
        @if($product->hero_image)
            <img src="{{ asset($product->hero_image) }}" alt="" class="w-full h-full object-cover">
        @else
            <x-ui.icon :name="$product->product_type->icon()" class="w-10 h-10 text-accent" />
        @endif
    </div>
    <p class="text-xs uppercase tracking-wide text-accent mb-1">{{ $product->product_type->label() }}</p>
    <h3 class="font-bold text-lg mb-2">{{ $product->title }}</h3>
    <p class="text-sm text-slate-400 mb-4 flex-1">{{ $product->short_description }}</p>
    <div class="flex items-center justify-between gap-3 mt-auto">
        <span class="font-bold text-white">₦{{ number_format($product->displayPrice(), 2) }}</span>
        <span class="text-sm text-accent font-semibold">View</span>
    </div>
</a>
