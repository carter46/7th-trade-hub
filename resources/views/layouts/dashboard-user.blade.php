<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $dashboardThemeResolved ?? 'light' }}" data-theme-preference="{{ $dashboardThemePreference ?? 'system' }}">
@include('partials.dashboard.shell-head', ['defaultTitle' => 'Dashboard'])
<body class="dashboard-shell bg-surface font-sans text-text-primary h-dvh overflow-hidden" data-dashboard-shell="user" x-data="mobileNav" @keydown.escape.window="open && close()">
    @include('partials.dashboard.shell-skip-link')

    <div class="relative flex h-full min-h-0 w-full flex-col">
        @if (session('impersonating'))
            @include('partials.impersonation-banner', [
                'impersonatorName' => $impersonatorName ?? null,
            ])
        @endif

        <x-dashboard.topbar context="Dashboard" :home-route="route('dashboard')" />

        <div class="relative flex min-h-0 flex-1">
            @include('partials.dashboard.shell-mobile-overlay')
            @include('partials.sidebar-user')

            <main id="main-content" class="min-h-0 min-w-0 flex-1 overflow-y-auto bg-surface p-4 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <x-dashboard.command-palette role="user" />
    <x-ui.toast />
    @stack('scripts')
    @RegisterServiceWorkerScript
</body>
</html>
