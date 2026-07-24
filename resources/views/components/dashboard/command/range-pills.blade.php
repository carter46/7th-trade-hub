{{--
  Single segmented range control (no duplicate select).
  Options: 24 Hours | 7 Days | 30 Days | 90 Days | Custom
--}}
@props([
    'value' => '24h',
    'options' => [],
    'name' => 'range',
])

@php
    $options = $options ?: \App\Services\Reporting\ReportingRange::presetOptions();
@endphp

<div {{ $attributes->class(['flex flex-wrap items-center gap-3']) }} data-command-range>
    <div class="inline-flex flex-wrap rounded-xl border border-slate-200 bg-slate-200/50 p-1 dark:border-border-default dark:bg-muted/40">
        @foreach ($options as $opt)
            @if (($opt['value'] ?? '') !== 'custom')
                <button
                    type="button"
                    data-range-value="{{ $opt['value'] }}"
                    class="rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all {{ $value === $opt['value'] ? 'bg-white text-slate-800 shadow-sm dark:bg-elevated dark:text-text-primary' : 'text-slate-500 hover:text-slate-700 dark:text-text-muted dark:hover:text-text-primary' }}"
                >{{ $opt['label'] }}</button>
            @endif
        @endforeach
        <button
            type="button"
            data-range-value="custom"
            class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all {{ $value === 'custom' ? 'bg-white text-slate-800 shadow-sm dark:bg-elevated dark:text-text-primary' : 'text-slate-500 hover:text-slate-700 dark:text-text-muted' }}"
        >Custom</button>
    </div>
    <input type="hidden" name="{{ $name }}" data-range-select value="{{ $value }}">
    <div data-custom-range class="{{ $value === 'custom' ? '' : 'hidden' }} flex flex-wrap items-center gap-2">
        <input type="date" name="from" data-range-from value="{{ request('from') }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:border-border-default dark:bg-elevated dark:text-text-primary">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">to</span>
        <input type="date" name="to" data-range-to value="{{ request('to') }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:border-border-default dark:bg-elevated dark:text-text-primary">
    </div>
</div>
