<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $dashboardThemeResolved ?? 'light' }}" data-theme-preference="{{ $dashboardThemePreference ?? 'system' }}">
@include('partials.dashboard.shell-head', ['defaultTitle' => 'Admin'])
<body class="dashboard-shell bg-surface font-sans text-text-primary h-dvh overflow-hidden" x-data="mobileNav" @keydown.escape.window="open && close()">
    @include('partials.dashboard.shell-skip-link')

    <div class="relative flex h-full min-h-0 w-full flex-col">
        <header class="h-16 shrink-0 flex items-center justify-between border-b border-border-default bg-header px-4 sm:px-6 lg:px-10 z-30">
            <div class="flex items-center gap-4 sm:gap-8">
                <button type="button" class="lg:hidden p-2 text-text-secondary hover:text-text-primary rounded-lg focus-ring" aria-label="Open menu" aria-controls="sidebar" @click="toggle()" :aria-expanded="open.toString()">
                    <x-ui.icon name="menu" class="w-6 h-6" />
                </button>
                <a href="{{ route('admin') }}" class="flex items-center gap-3 text-primary">
                    <x-dashboard.asset key="logo" class="h-8 w-auto" alt="{{ config('app.name') }}" />
                    <h2 class="text-text-primary text-lg font-bold leading-tight tracking-tight">Admin</h2>
                </a>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                <x-dashboard.theme-switcher />
                <a href="{{ route('dashboard.notifications') }}" class="relative flex size-10 items-center justify-center rounded-xl bg-muted/40 text-text-secondary hover:text-primary transition-colors focus-ring" aria-label="Notifications">
                    <x-ui.icon name="notifications" class="w-5 h-5" />
                    @php $unread = auth()->user()->unreadNotificationsCount(); @endphp
                    @if($unread > 0)
                    <span class="absolute top-1 right-1 w-2 h-2 bg-primary rounded-full"></span>
                    @endif
                </a>
                <a href="{{ route('profile.edit') }}" class="flex size-10 items-center justify-center rounded-xl bg-muted/40 text-text-secondary hover:text-primary transition-colors focus-ring" aria-label="Settings">
                    <x-ui.icon name="settings" class="w-5 h-5" />
                </a>
                <div class="hidden sm:block h-10 w-px bg-border-default mx-1"></div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-bold leading-none text-text-primary">{{ auth()->user()->name ?? auth()->user()->email }}</p>
                        <p class="text-xs text-text-secondary mt-1">Admin</p>
                    </div>
                    <div class="bg-primary/20 aspect-square rounded-full size-10 border-2 border-primary/20 flex items-center justify-center">
                        <x-ui.icon name="user" class="w-5 h-5 text-primary" />
                    </div>
                </div>
            </div>
        </header>

        <div class="flex min-h-0 flex-1 relative">
            @include('partials.dashboard.shell-mobile-overlay')

            @include('partials.sidebar-admin')

            <main id="main-content" class="min-h-0 flex-1 overflow-y-auto p-4 lg:p-8 min-w-0 bg-surface">
                @yield('content')
            </main>
        </div>
    </div>

    <x-ui.toast />
    @stack('scripts')
    @RegisterServiceWorkerScript
</body>
</html>
