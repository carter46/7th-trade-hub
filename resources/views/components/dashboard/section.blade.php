@props([
    'title' => null,
])
<section {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @if ($title || isset($actions))
        <div class="flex flex-wrap items-center justify-between gap-3">
            @if ($title)
                <h2 class="text-lg font-semibold text-text-primary">{{ $title }}</h2>
            @endif
            @isset($actions)
                <div class="flex flex-wrap gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    {{ $slot }}
</section>
