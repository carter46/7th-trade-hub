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
        x-ref="trigger"
        class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg text-text-secondary hover:bg-muted/60 hover:text-text-primary focus-ring"
        @click.stop="toggle()"
        :aria-expanded="open.toString()"
        aria-haspopup="menu"
        aria-label="Row actions"
    >
        <span class="text-lg leading-none" aria-hidden="true">⋮</span>
    </button>

    <template x-teleport="body">
        <div
            x-ref="menu"
            x-show="open"
            x-cloak
            x-bind:style="`position:fixed;z-index:70;top:${menuStyle.top};left:${menuStyle.left};min-width:${menuStyle.minWidth}`"
            @click.outside="close()"
            class="rounded-xl border border-border-default bg-surface p-1 shadow-panel"
            role="menu"
            @click="close()"
        >
            {{ $slot }}
        </div>
    </template>
</div>
