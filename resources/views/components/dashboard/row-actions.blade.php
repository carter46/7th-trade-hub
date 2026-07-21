@props([
    'align' => 'right',
])

<div
    {{ $attributes->class(['relative inline-flex']) }}
    x-data="rowActions()"
    @keydown.escape.window="open && close()"
>
    <button
        type="button"
        class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg text-text-secondary hover:bg-muted/60 hover:text-text-primary focus-ring"
        @click="toggle()"
        :aria-expanded="open.toString()"
        aria-haspopup="menu"
        aria-label="Row actions"
    >
        <span class="text-lg leading-none" aria-hidden="true">⋮</span>
    </button>

    <div
        x-show="open"
        x-cloak
        @click.outside="close()"
        class="absolute z-40 mt-1 min-w-[11rem] rounded-xl border border-border-default bg-surface p-1 shadow-panel {{ $align === 'left' ? 'left-0' : 'right-0' }}"
        role="menu"
    >
        {{ $slot }}
    </div>
</div>
