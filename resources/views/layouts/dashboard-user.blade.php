<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $dashboardThemeResolved ?? 'light' }}" data-theme-preference="{{ $dashboardThemePreference ?? 'system' }}">
@include('partials.dashboard.shell-head', ['defaultTitle' => 'Dashboard'])
<body class="dashboard-shell min-h-0 h-dvh flex overflow-hidden bg-surface font-sans text-text-primary" x-data="mobileNav" @keydown.escape.window="open && close()">
    @include('partials.dashboard.shell-skip-link')

    @include('partials.dashboard.shell-mobile-overlay')

    @include('partials.sidebar-user')

    <main id="main-content" class="flex-1 flex flex-col min-w-0 min-h-0 bg-surface overflow-y-auto">
        <header class="h-16 shrink-0 bg-header sticky top-0 z-30 flex items-center justify-between px-4 sm:px-8 border-b border-border-default">
            <div class="flex items-center gap-4 lg:hidden">
                <button type="button" class="inline-flex min-h-11 min-w-11 items-center justify-center text-text-secondary hover:text-text-primary focus-ring rounded-lg" aria-label="Open menu" aria-controls="sidebar" @click="toggle()" :aria-expanded="open.toString()">
                    <x-ui.icon name="menu" class="w-6 h-6" />
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center">
                    <x-dashboard.asset key="logo" class="h-8 w-auto" alt="{{ config('app.name') }}" />
                </a>
            </div>
            <div class="flex-1 hidden md:block"></div>
            <div class="flex items-center gap-2 sm:gap-4">
                <div class="hidden lg:block" data-desktop-theme-switcher>
                    <x-dashboard.theme-switcher />
                </div>
                <a href="{{ route('dashboard.notifications') }}" class="relative p-2 text-text-secondary hover:text-text-primary transition-colors focus-ring rounded-lg" aria-label="Notifications">
                    <x-ui.icon name="notifications" class="w-5 h-5" />
                    @php $unread = auth()->user()->unreadNotificationsCount(); @endphp
                    @if($unread > 0)
                    <span class="absolute top-1 right-1 w-2 h-2 bg-primary rounded-full"></span>
                    @endif
                </a>
                <div class="flex items-center gap-3 pl-4 sm:pl-6 border-l border-border-default">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-text-primary">{{ auth()->user()->name ?? auth()->user()->email }}</p>
                        <p class="text-xs text-text-secondary">Member</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="w-10 h-10 rounded-full bg-gradient-primary border-2 border-border-default flex items-center justify-center font-bold text-white overflow-hidden focus-ring" aria-label="Profile">
                        <x-ui.icon name="user" class="w-5 h-5" />
                    </a>
                </div>
            </div>
        </header>

        <div class="p-4 sm:p-8">
            @yield('content')
        </div>
    </main>

    <x-ui.toast />
    @stack('scripts')
    @RegisterServiceWorkerScript
</body>
</html>
