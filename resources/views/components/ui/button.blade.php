@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconRight' => null,
    'loading' => false,
    'disabled' => false,
])

@php
    $variants = [
        'primary' => 'bg-primary text-white hover:bg-primary/90 border border-transparent',
        'secondary' => 'bg-elevated text-text-primary hover:bg-muted border border-border-default',
        'ghost' => 'bg-transparent text-text-secondary hover:text-text-primary hover:bg-muted/50 border border-transparent',
        'danger' => 'bg-danger text-white hover:bg-danger/90 border border-transparent',
        'warning' => 'bg-warning text-slate-900 hover:bg-warning/90 border border-transparent',
        'success' => 'bg-success text-white hover:bg-success/90 border border-transparent',
        'link' => 'bg-transparent text-primary hover:underline border-0 p-0 h-auto',
    ];

    $sizes = [
        'sm' => 'h-9 px-3 text-xs gap-1.5',
        'md' => 'h-10 px-4 text-sm gap-2',
        'lg' => 'h-11 px-5 text-sm gap-2',
        'xs' => 'h-8 px-2.5 text-xs gap-1',
    ];

    $base = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus-ring disabled:opacity-50 disabled:pointer-events-none';
    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
    $isDisabled = $disabled || $loading;
@endphp

@if ($href && ! $isDisabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <x-ui.icon name="spinner" class="w-4 h-4 animate-spin" />
        @elseif ($icon)
            <x-ui.icon :name="$icon" class="w-4 h-4" />
        @endif
        <span>{{ $slot }}</span>
        @if ($iconRight && ! $loading)
            <x-ui.icon :name="$iconRight" class="w-4 h-4" />
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($isDisabled)
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($attributes->has('x-bind:loading') === false && $loading) aria-busy="true" @endif
    >
        @if ($loading)
            <x-ui.icon name="spinner" class="w-4 h-4 animate-spin" />
        @elseif ($icon)
            <x-ui.icon :name="$icon" class="w-4 h-4" />
        @endif
        <span>{{ $slot }}</span>
        @if ($iconRight && ! $loading)
            <x-ui.icon :name="$iconRight" class="w-4 h-4" />
        @endif
    </button>
@endif
