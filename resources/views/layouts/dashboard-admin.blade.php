<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') | {{ config('app.name') }}</title>
    @PwaHead

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface font-sans text-text-primary min-h-screen" x-data="mobileNav" @keydown.escape.window="close()">
    <div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
        <header class="h-16 flex items-center justify-between border-b border-border-default bg-elevated px-4 sm:px-6 lg:px-10 sticky top-0 z-30">
            <div class="flex items-center gap-4 sm:gap-8">
                <button type="button" class="lg:hidden p-2 text-text-secondary hover:text-text-primary rounded-lg focus-ring" aria-label="Open menu" @click="toggle()" :aria-expanded="open.toString()">
                    <x-ui.icon name="menu" class="w-6 h-6" />
                </button>
                <a href="{{ route('admin') }}" class="flex items-center gap-3 text-primary">
                    <div class="size-8 flex items-center justify-center bg-primary/10 rounded-lg">
                        <x-ui.icon name="dashboard" class="w-5 h-5 text-primary" />
                    </div>
                    <h2 class="text-text-primary text-lg font-bold leading-tight tracking-tight">Admin</h2>
                </a>
            </div>
            <div class="flex items-center gap-3 sm:gap-4">
                <a href="{{ route('dashboard.notifications') }}" class="flex size-10 items-center justify-center rounded-xl bg-muted/40 text-text-secondary hover:text-primary transition-colors focus-ring" aria-label="Notifications">
                    <x-ui.icon name="notifications" class="w-5 h-5" />
                </a>
                <a href="{{ route('profile.edit') }}" class="flex size-10 items-center justify-center rounded-xl bg-muted/40 text-text-secondary hover:text-primary transition-colors focus-ring" aria-label="Settings">
                    <x-ui.icon name="settings" class="w-5 h-5" />
                </a>
                <div class="hidden sm:block h-10 w-px bg-border-default mx-1"></div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-bold leading-none">{{ auth()->user()->name ?? auth()->user()->email }}</p>
                        <p class="text-xs text-text-secondary mt-1">Admin</p>
                    </div>
                    <div class="bg-primary/20 aspect-square rounded-full size-10 border-2 border-primary/20 flex items-center justify-center">
                        <x-ui.icon name="user" class="w-5 h-5 text-primary" />
                    </div>
                </div>
            </div>
        </header>

        <div class="flex flex-1 relative">
            <div
                x-show="open"
                x-cloak
                class="fixed inset-0 z-40 bg-black/60 lg:hidden"
                @click="close()"
                aria-hidden="true"
            ></div>

            @include('partials.sidebar-admin')

            <main class="flex-1 overflow-y-auto p-4 lg:p-8 min-w-0">
                @yield('content')
            </main>
        </div>
    </div>

    <x-ui.toast />
    @stack('scripts')
    @RegisterServiceWorkerScript
</body>
</html>
