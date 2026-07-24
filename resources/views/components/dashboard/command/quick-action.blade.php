@props([
    'title',
    'subtitle' => null,
    'icon' => 'plus',
    'href' => '#',
    'accent' => 'emerald',
])

@php
    $iconWrap = [
        'emerald' => 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600',
        'blue' => 'bg-blue-50 text-blue-600 group-hover:bg-blue-600',
        'amber' => 'bg-amber-50 text-amber-600 group-hover:bg-amber-600',
        'indigo' => 'bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600',
    ];
    $wrap = $iconWrap[$accent] ?? $iconWrap['emerald'];
@endphp

<a href="{{ $href }}" {{ $attributes->class([
    'group flex flex-col items-start rounded-xl border border-border-default bg-elevated p-4 text-left transition-all hover:border-brand hover:shadow-md',
]) }}>
    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg {{ $wrap }} group-hover:text-white transition-colors">
        <x-dashboard.icon :name="$icon" class="h-5 w-5" />
    </div>
    <span class="text-sm font-bold text-text-primary">{{ $title }}</span>
    @if ($subtitle)
        <span class="mt-1 text-[11px] text-text-muted">{{ $subtitle }}</span>
    @endif
</a>
