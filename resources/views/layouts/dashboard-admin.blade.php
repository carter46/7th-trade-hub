<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $dashboardThemeResolved ?? 'light' }}" data-theme-preference="{{ $dashboardThemePreference ?? 'light' }}">
@include('partials.dashboard.shell-head', ['defaultTitle' => 'Admin'])
<body class="dashboard-shell bg-surface font-sans text-text-primary h-dvh overflow-hidden" data-dashboard-shell="admin" x-data="mobileNav" @keydown.escape.window="open && close()">
    @include('partials.dashboard.shell-skip-link')

    <div class="relative flex h-full min-h-0 w-full flex-col">
        <x-dashboard.topbar context="Admin" :home-route="route('admin')" />

        <div class="relative flex min-h-0 flex-1">
            @include('partials.dashboard.shell-mobile-overlay')
            @include('partials.sidebar-admin')

            <x-dashboard.scroll-main>
                @yield('content')
            </x-dashboard.scroll-main>
        </div>
    </div>

    <x-dashboard.command-palette role="admin" />
    <x-dashboard.media-library-modal />
    <x-ui.toast />
    @stack('scripts')
    @RegisterServiceWorkerScript
</body>
</html>
