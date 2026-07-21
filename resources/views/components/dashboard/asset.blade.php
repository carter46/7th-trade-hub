@props([
    'key' => 'logo',
    'alt' => '',
    'class' => 'h-8 w-auto',
])
@php
    /** @var \App\Services\ThemeManager $themes */
    $themes = app(\App\Services\ThemeManager::class);
    $resolved = $dashboardThemeResolved ?? $themes->fallbackTheme();
    $light = $themes->asset($key, 'light');
    $dark = $themes->asset($key, 'dark');
    $current = $themes->asset($key, $resolved);
@endphp
@if ($current)
<img
    {{ $attributes->merge(['class' => $class, 'alt' => $alt]) }}
    src="{{ asset($current) }}"
    data-theme-asset="{{ $key }}"
    data-src-light="{{ $light ? asset($light) : '' }}"
    data-src-dark="{{ $dark ? asset($dark) : '' }}"
    x-data
    x-init="
        const sync = (theme) => {
            const src = theme === 'dark' ? ($el.dataset.srcDark || $el.dataset.srcLight) : ($el.dataset.srcLight || $el.dataset.srcDark);
            if (src) $el.src = src;
        };
        sync(document.documentElement.getAttribute('data-theme') || 'light');
        window.addEventListener('dashboard-theme-changed', (e) => sync(e.detail?.resolved || 'light'));
    "
>
@else
    <div {{ $attributes->merge(['class' => 'w-8 h-8 bg-primary rounded-lg flex items-center justify-center font-bold text-white '.$class]) }}>7</div>
@endif
