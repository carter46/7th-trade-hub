@props([
    'preference' => null,
])
@php
    $preference = $preference ?? ($dashboardThemePreference ?? 'system');
@endphp
<div
    class="inline-flex items-center rounded-xl border border-border-default bg-muted/40 p-1"
    role="group"
    aria-label="Theme"
    x-data="themeSwitcher(@js($preference))"
>
    <button
        type="button"
        class="inline-flex min-h-11 min-w-11 items-center justify-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-colors focus-ring sm:min-h-0 sm:min-w-0"
        :class="preference === 'light' ? 'bg-elevated text-text-primary shadow-sm' : 'text-text-secondary hover:text-text-primary'"
        @click="choose('light')"
        :aria-pressed="(preference === 'light').toString()"
        title="Light"
    >
        <x-ui.icon name="sun" class="w-3.5 h-3.5" />
        <span class="hidden sm:inline">Light</span>
    </button>
    <button
        type="button"
        class="inline-flex min-h-11 min-w-11 items-center justify-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-colors focus-ring sm:min-h-0 sm:min-w-0"
        :class="preference === 'dark' ? 'bg-elevated text-text-primary shadow-sm' : 'text-text-secondary hover:text-text-primary'"
        @click="choose('dark')"
        :aria-pressed="(preference === 'dark').toString()"
        title="Dark"
    >
        <x-ui.icon name="moon" class="w-3.5 h-3.5" />
        <span class="hidden sm:inline">Dark</span>
    </button>
    <button
        type="button"
        class="inline-flex min-h-11 min-w-11 items-center justify-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-colors focus-ring sm:min-h-0 sm:min-w-0"
        :class="preference === 'system' ? 'bg-elevated text-text-primary shadow-sm' : 'text-text-secondary hover:text-text-primary'"
        @click="choose('system')"
        :aria-pressed="(preference === 'system').toString()"
        title="System"
    >
        <x-ui.icon name="monitor" class="w-3.5 h-3.5" />
        <span class="hidden sm:inline">System</span>
    </button>
</div>
