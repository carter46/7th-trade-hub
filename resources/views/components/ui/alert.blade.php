@props([
    'type' => 'info',
    'title' => null,
])

@php
    $styles = [
        'info' => 'border-blue-500/40 bg-blue-500/10 text-blue-200',
        'success' => 'border-success/40 bg-success/10 text-green-200',
        'warning' => 'border-warning/40 bg-warning/10 text-amber-200',
        'error' => 'border-danger/40 bg-danger/10 text-red-200',
    ];
    $icons = [
        'info' => 'info',
        'success' => 'check',
        'warning' => 'warning',
        'error' => 'x',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-3 rounded-xl border p-4 ' . ($styles[$type] ?? $styles['info']), 'role' => 'alert']) }}>
    <x-ui.icon :name="$icons[$type] ?? 'info'" class="w-5 h-5 shrink-0 mt-0.5" />
    <div class="min-w-0">
        @if ($title)
            <p class="text-sm font-semibold">{{ $title }}</p>
        @endif
        <div class="text-sm opacity-90">{{ $slot }}</div>
    </div>
</div>
