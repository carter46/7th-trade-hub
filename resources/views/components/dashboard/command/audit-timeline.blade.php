@props([
    'entries' => [],
    'consoleUrl' => null,
    'title' => 'Security Audit',
])

@php
    $toneBorder = [
        'red' => 'border-red-500 text-red-500',
        'blue' => 'border-blue-500 text-blue-500',
        'brand' => 'border-primary text-primary',
        'slate' => 'border-slate-300 text-slate-400',
    ];
    $grouped = collect($entries)->groupBy(fn ($e) => $e['day_label'] ?? 'Recent');
@endphp

<div {{ $attributes->class(['rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-border-default dark:bg-elevated']) }}>
    <h3 class="mb-6 text-xs font-black uppercase tracking-widest text-slate-400">{{ $title }}</h3>
    <div class="space-y-6">
        @forelse ($grouped as $day => $items)
            <div>
                <p class="mb-3 text-[9px] font-black uppercase tracking-widest text-slate-300">{{ $day }}</p>
                <div class="relative space-y-5 before:absolute before:inset-y-0 before:left-[11px] before:w-px before:bg-slate-100 dark:before:bg-border-subtle">
                    @foreach ($items as $entry)
                        @php $tone = $toneBorder[$entry['tone'] ?? 'slate'] ?? $toneBorder['slate']; @endphp
                        <div class="relative flex gap-4">
                            <div class="relative z-10 flex h-6 w-6 items-center justify-center rounded-full border-2 bg-white dark:bg-elevated {{ $tone }}">
                                <x-dashboard.icon :name="in_array($entry['icon'] ?? '', ['person','history','settings','audit'], true) ? ($entry['icon'] ?? 'history') : 'history'" class="h-3 w-3" />
                            </div>
                            <div class="flex-1 pb-1">
                                <p class="text-[11px] font-bold leading-none text-slate-900 dark:text-text-primary">{{ $entry['action'] ?? '' }}</p>
                                <p class="mt-1 text-[10px] text-slate-500">{{ $entry['actor'] ?? '' }}</p>
                                <span class="mt-2 block text-[9px] text-slate-400">{{ $entry['when'] ?? '' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-xs text-slate-400">No recent audit activity</p>
        @endforelse
    </div>
    @if ($consoleUrl)
        <a href="{{ $consoleUrl }}" class="mt-6 block w-full rounded-lg border border-slate-100 py-2 text-center text-[10px] font-black uppercase tracking-widest text-slate-400 transition-colors hover:text-primary dark:border-border-subtle">Full Audit Console</a>
    @endif
</div>
