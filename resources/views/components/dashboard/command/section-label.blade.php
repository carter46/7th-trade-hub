{{--
  Command-center accents (map to dashboard theme, not a second design system):
  emerald = revenue / positive · blue = traffic · amber = KYC / warning
  indigo = escrows · orange = support · red = alerts
--}}
@props([
    'title',
    'accent' => 'emerald',
    'action' => null,
    'actionHref' => null,
])

@php
    $accents = [
        'emerald' => 'bg-emerald-500',
        'blue' => 'bg-blue-500',
        'amber' => 'bg-amber-500',
        'indigo' => 'bg-indigo-500',
        'orange' => 'bg-orange-500',
    ];
    $bar = $accents[$accent] ?? $accents['emerald'];
@endphp

<div {{ $attributes->class(['flex items-center justify-between mb-4']) }}>
    <div class="flex items-center gap-2">
        <div class="h-4 w-1 rounded-full {{ $bar }}"></div>
        <h2 class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-text-muted">{{ $title }}</h2>
    </div>
    @if ($action && $actionHref)
        <a href="{{ $actionHref }}" class="text-[10px] font-bold uppercase tracking-wider text-primary hover:underline dark:text-brand">{{ $action }}</a>
    @endif
</div>
