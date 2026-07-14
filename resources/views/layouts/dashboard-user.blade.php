<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    @PwaHead

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex overflow-hidden bg-surface font-sans text-text-primary" x-data="mobileNav" @keydown.escape.window="close()">
    {{-- Mobile drawer overlay --}}
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-40 bg-black/60 lg:hidden"
        @click="close()"
        aria-hidden="true"
    ></div>

    @include('partials.sidebar-user')

    <main class="flex-1 flex flex-col min-w-0 bg-surface overflow-y-auto">
        <header class="h-16 glass-card sticky top-0 z-30 flex items-center justify-between px-4 sm:px-8 border-b border-border-default">
            <div class="flex items-center gap-4 lg:hidden">
                <button type="button" class="p-2 text-text-secondary hover:text-text-primary focus-ring rounded-lg" aria-label="Open menu" @click="toggle()" :aria-expanded="open.toString()">
                    <x-ui.icon name="menu" class="w-6 h-6" />
                </button>
                <a href="{{ route('dashboard') }}" class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center font-bold text-white">7</a>
            </div>
            <div class="flex-1 hidden md:block"></div>
            <div class="flex items-center gap-4 sm:gap-6">
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
