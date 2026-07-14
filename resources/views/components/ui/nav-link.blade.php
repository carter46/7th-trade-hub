@props([
    'href',
    'icon' => null,
    'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'flex items-center gap-3 px-4 py-3 rounded-xl transition-all ' .
            ($active
                ? 'sidebar-link-active'
                : 'text-text-secondary hover:text-text-primary hover:bg-muted/50'),
    ]) }}
>
    @if ($icon)
        <x-ui.icon :name="$icon" class="w-[22px] h-[22px]" />
    @endif
    <span class="text-sm font-medium">{{ $slot }}</span>
</a>
