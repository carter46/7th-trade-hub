@props([
    'striped' => true,
    'loading' => false,
    'empty' => false,
    'emptyTitle' => 'No records yet',
    'emptyDescription' => 'There’s nothing to show here right now.',
    'emptyIcon' => 'empty',
    'emptyAction' => null,
    'minHeight' => true,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-border-default bg-elevated shadow-panel']) }}>
    @isset($toolbar)
        <div class="border-b border-border-default p-4 flex flex-wrap items-center gap-3">{{ $toolbar }}</div>
    @endisset

    @isset($filters)
        <div class="border-b border-border-default p-4">{{ $filters }}</div>
    @endisset

    @isset($bulk)
        <div class="border-b border-border-default p-4">{{ $bulk }}</div>
    @endisset

    <div class="overflow-x-auto {{ $minHeight ? 'min-h-[320px]' : '' }}">
        @if ($loading)
            <x-ui.skeleton.table :rows="5" :cols="5" />
        @elseif ($empty)
            <x-dashboard.empty-state
                :icon="$emptyIcon"
                :title="$emptyTitle"
                :description="$emptyDescription"
                :action="$emptyAction"
            />
        @else
            <table class="w-full text-sm text-left">
                @isset($head)
                    <thead class="sticky top-0 z-10 bg-surface-secondary text-text-secondary">
                        <tr>{{ $head }}</tr>
                    </thead>
                @endisset
                <tbody class="{{ $striped ? '[&>tr:nth-child(even)]:bg-[var(--th-table-stripe)]' : '' }} [&>tr:hover]:bg-[var(--th-table-hover)] divide-y divide-border-default">
                    {{ $slot }}
                </tbody>
            </table>
        @endif
    </div>

    @isset($footer)
        <div class="border-t border-border-default p-4">{{ $footer }}</div>
    @endisset
</div>
