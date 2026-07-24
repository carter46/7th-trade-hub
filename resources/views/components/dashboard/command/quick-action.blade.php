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
        'orange' => 'bg-orange-50 text-orange-600 group-hover:bg-orange-600',
    ];
    $wrap = $iconWrap[$accent] ?? $iconWrap['emerald'];
@endphp

<a href="{{ $href }}" {{ $attributes->class([
    'group flex flex-col items-start rounded-xl border border-slate-200 bg-white p-5 text-left shadow-sm transition-all hover:-translate-y-0.5 hover:border-primary hover:shadow-md dark:border-border-default dark:bg-elevated',
]) }}>
    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg {{ $wrap }} group-hover:text-white transition-colors">
        <x-dashboard.icon :name="$icon" class="h-5 w-5" />
    </div>
    <span class="text-sm font-bold text-slate-900 dark:text-text-primary">{{ $title }}</span>
    @if ($subtitle)
        <span class="mt-1.5 text-[11px] leading-relaxed text-slate-500 dark:text-text-muted">{{ $subtitle }}</span>
    @endif
</a>
