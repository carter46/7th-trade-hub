@props([
    'href',
])

@php
    $brandName = $siteBranding['site_short_name'] ?? $siteName ?? config('app.name');
    $themes = app(\App\Services\ThemeManager::class);
    $resolved = $dashboardThemeResolved ?? $themes->fallbackTheme();
    $logoUrl = $themes->asset('logo', $resolved);
@endphp

<a href="{{ $href }}" class="flex min-w-0 items-center" aria-label="{{ $brandName }}">
    @if(filled($logoUrl))
        <x-dashboard.asset
            key="logo"
            class="h-11 w-auto max-w-[11.5rem] object-contain object-left"
            :alt="$brandName"
        />
        <span class="sr-only">{{ $brandName }}</span>
    @else
        <span class="truncate text-xl font-bold tracking-tight text-text-primary">{{ $brandName }}</span>
    @endif
</a>
