@props([
    'context' => 'Dashboard',
    'homeRoute' => null,
    'destinations' => [],
])

@php
    $homeRoute = $homeRoute ?? (auth()->user()?->hasRole('admin') ? route('admin') : route('dashboard'));
    $role = auth()->user()?->hasRole('admin') ? 'admin' : 'user';
    $accountPrefix = $role === 'admin' ? 'admin.account' : 'dashboard.account';
    $destinations = $destinations ?: \App\Support\DashboardNavigation::searchIndex($role, auth()->user());
@endphp

<header class="h-16 shrink-0 border-b border-border-default bg-header z-30">
    {{-- Mobile topbar: menu, context, search, notifications, account --}}
    <div class="flex h-full items-center justify-between gap-2 px-4 lg:hidden">
        <div class="flex min-w-0 items-center gap-2">
            <button
                type="button"
                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-text-secondary hover:text-text-primary focus-ring"
                aria-label="Open menu"
                aria-controls="sidebar"
                @click="toggle()"
                :aria-expanded="open.toString()"
            >
                <x-ui.icon name="menu" class="w-6 h-6" />
            </button>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-text-primary">{{ $context }}</p>
            </div>
        </div>
        <div class="flex items-center gap-1">
            <button
                type="button"
                class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg text-text-secondary hover:text-text-primary focus-ring"
                aria-label="Search navigation"
                @click="open = true; setTimeout(() => document.querySelector('[data-dashboard-nav-search]')?.focus(), 80)"
            >
                <x-ui.icon name="search" class="w-5 h-5" />
            </button>
            <x-dashboard.notification-menu />
            <x-dashboard.account-menu :prefix="$accountPrefix" compact />
        </div>
    </div>

    {{-- Desktop topbar --}}
    <div class="hidden h-full items-center justify-between gap-4 px-4 sm:px-6 lg:flex lg:px-10">
        <div class="flex items-center gap-4 sm:gap-8">
            <a href="{{ $homeRoute }}" class="flex items-center gap-3 text-primary">
                <x-dashboard.asset key="logo" class="h-8 w-auto" alt="{{ config('app.name') }}" />
                <h2 class="text-lg font-bold leading-tight tracking-tight text-text-primary">{{ $context }}</h2>
            </a>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="hidden lg:block" data-desktop-theme-switcher>
                <x-dashboard.theme-switcher />
            </div>
            <x-dashboard.notification-menu />
            <div class="hidden sm:block mx-1 h-10 w-px bg-border-default"></div>
            <x-dashboard.account-menu :prefix="$accountPrefix" />
        </div>
    </div>
</header>
