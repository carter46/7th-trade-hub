<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $dashboardThemeResolved ?? 'light' }}" data-theme-preference="{{ $dashboardThemePreference ?? 'system' }}">
@include('partials.dashboard.shell-head', ['defaultTitle' => 'Dashboard'])
<body class="dashboard-shell flex min-h-0 h-dvh overflow-hidden bg-surface font-sans text-text-primary" data-dashboard-shell="user" x-data="mobileNav" @keydown.escape.window="open && close()">
    @include('partials.dashboard.shell-skip-link')
    @include('partials.dashboard.shell-mobile-overlay')
    @include('partials.sidebar-user')

    <main id="main-content" class="flex min-h-0 min-w-0 flex-1 flex-col overflow-y-auto bg-surface">
        <x-dashboard.topbar context="Dashboard" :home-route="route('dashboard')" />

        <div class="p-4 lg:p-8">
            @yield('content')
        </div>
    </main>

    <x-dashboard.command-palette role="user" />
    <x-ui.toast />
    @stack('scripts')
    @RegisterServiceWorkerScript
</body>
</html>
