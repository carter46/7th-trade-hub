@props([
    'tabs' => [],
    'active' => null,
    'variant' => 'underline', // underline | pills
])

@php
    /** @var array<int, array{label: string, href: string, id?: string, count?: int|null}> $tabs */
@endphp

<div
    {{ $attributes->merge(['class' => ($variant === 'pills' ? 'relative flex gap-2 overflow-x-auto pb-1' : 'relative flex flex-wrap gap-1 border-b border-border-default')]) }}
    role="navigation"
    aria-label="Section tabs"
    :aria-busy="loading ? 'true' : null"
    x-data="dashboardAjaxTabs(@js($active))"
    @dashboard-tab-navigated.window="activeId = $event.detail.id"
>
    @foreach ($tabs as $tab)
        @php
            $id = $tab['id'] ?? \Illuminate\Support\Str::slug($tab['label']);
            $isActive = ($active ?? null) === $id || (($active ?? null) === null && $loop->first);
        @endphp
        <a
            href="{{ $tab['href'] }}"
            data-tab-id="{{ $id }}"
            @click.prevent="navigate($event, '{{ $tab['href'] }}', '{{ $id }}')"
            :aria-current="activeId === '{{ $id }}' ? 'page' : null"
            @class([
                'inline-flex min-h-11 shrink-0 items-center gap-2 px-4 py-2 text-sm font-medium transition-colors focus-ring',
                $variant === 'pills'
                    ? 'rounded-xl border font-semibold'
                    : 'border-b-2',
                $isActive
                    ? ($variant === 'pills'
                        ? 'border-primary bg-primary/10 text-primary'
                        : 'border-primary text-primary')
                    : ($variant === 'pills'
                        ? 'border-border-default bg-elevated text-text-secondary hover:text-text-primary'
                        : 'border-transparent text-text-secondary hover:text-text-primary'),
            ])
            :class="activeId === '{{ $id }}'
                ? (@js($variant === 'pills')
                    ? 'border-primary bg-primary/10 text-primary'
                    : 'border-primary text-primary')
                : (@js($variant === 'pills')
                    ? 'border-border-default bg-elevated text-text-secondary hover:text-text-primary'
                    : 'border-transparent text-text-secondary hover:text-text-primary')"
        >
            <span>{{ $tab['label'] }}</span>
            @if (isset($tab['count']))
                <span class="rounded-full bg-muted px-2 py-0.5 text-[11px] text-text-muted">{{ $tab['count'] }}</span>
            @endif
        </a>
    @endforeach
    <span
        class="pointer-events-none ml-auto inline-flex items-center gap-1 self-center text-xs text-text-muted"
        x-show="loading"
        x-cloak
        aria-live="polite"
    >
        <x-ui.icon name="spinner" class="h-3.5 w-3.5 animate-spin" />
        Loading…
    </span>
</div>
