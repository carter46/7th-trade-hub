@props([
    'value' => '7d',
    'options' => [],
    'name' => 'range',
])

@php
    $options = $options ?: \App\Services\Reporting\ReportingRange::presetOptions();
    $quick = collect($options)->whereIn('value', ['today', '24h', '7d', '30d'])->values();
@endphp

<div {{ $attributes->class(['flex flex-wrap items-center gap-2']) }} data-command-range>
    <div class="flex rounded-lg border border-border-default bg-muted/40 p-1">
        @foreach ($quick as $opt)
            <button
                type="button"
                data-range-value="{{ $opt['value'] }}"
                class="rounded px-3 py-1 text-xs font-bold transition-all {{ $value === $opt['value'] ? 'bg-elevated text-text-primary shadow-sm' : 'text-text-muted hover:text-text-primary' }}"
            >{{ $opt['label'] }}</button>
        @endforeach
    </div>
    <select
        name="{{ $name }}"
        data-range-select
        class="rounded-lg border border-border-default bg-elevated px-3 py-2 text-xs font-bold text-text-primary"
    >
        @foreach ($options as $opt)
            <option value="{{ $opt['value'] }}" @selected($value === $opt['value'])>{{ $opt['label'] }}</option>
        @endforeach
    </select>
    <div data-custom-range class="{{ $value === 'custom' ? '' : 'hidden' }} flex items-center gap-2">
        <input type="date" name="from" data-range-from value="{{ request('from') }}" class="rounded-lg border border-border-default bg-elevated px-2 py-1.5 text-xs">
        <input type="date" name="to" data-range-to value="{{ request('to') }}" class="rounded-lg border border-border-default bg-elevated px-2 py-1.5 text-xs">
    </div>
</div>
