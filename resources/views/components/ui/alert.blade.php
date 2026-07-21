@props([
    'type' => 'info',
    'title' => null,
])

@php
    $styles = [
        'info' => 'border-primary/30 bg-primary/10 text-text-primary',
        'success' => 'border-success/40 bg-success/10 text-text-primary',
        'warning' => 'border-warning/40 bg-warning/10 text-text-primary',
        'error' => 'border-danger/40 bg-danger/10 text-text-primary',
    ];
    $icons = [
        'info' => 'info',
        'success' => 'check',
        'warning' => 'warning',
        'error' => 'x',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-3 rounded-xl border p-4 ' . ($styles[$type] ?? $styles['info']), 'role' => 'alert']) }}>
    <x-ui.icon :name="$icons[$type] ?? 'info'" class="w-5 h-5 shrink-0 mt-0.5 text-primary" />
    <div class="min-w-0">
        @if ($title)
            <p class="text-sm font-semibold text-text-primary">{{ $title }}</p>
        @endif
        <div class="text-sm text-text-secondary">{{ $slot }}</div>
    </div>
</div>
