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
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex overflow-hidden bg-dark font-sans text-slate-100">
    @include('partials.sidebar-user')

    <main class="flex-1 flex flex-col min-w-0 bg-[#0F172A] overflow-y-auto">
        <header class="h-20 glass-card sticky top-0 z-30 flex items-center justify-between px-8 border-b border-slate-800">
            <div class="flex items-center gap-4 lg:hidden">
                <button type="button" class="p-2 text-slate-400 hover:text-white" aria-label="Menu">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <a href="{{ route('dashboard') }}" class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center font-bold text-white">7</a>
            </div>
            <div class="flex-1 max-w-xl hidden md:block">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-500">
                        <span class="material-symbols-outlined text-lg">search</span>
                    </span>
                    <input class="block w-full pl-10 pr-3 py-2 border border-slate-700 rounded-lg leading-5 bg-slate-800/50 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm" placeholder="Search services, orders..." type="text"/>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="relative p-2 text-slate-400 hover:text-white transition-colors" aria-label="Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                </a>
                <div class="flex items-center gap-3 pl-6 border-l border-slate-700">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-white">{{ auth()->user()->name ?? auth()->user()->email }}</p>
                        <p class="text-xs text-slate-400">Member</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="w-10 h-10 rounded-full bg-gradient-primary border-2 border-slate-700 flex items-center justify-center font-bold text-white overflow-hidden">
                        <span class="material-symbols-outlined">person</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="p-8 space-y-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
    @RegisterServiceWorkerScript
</body>
</html>
