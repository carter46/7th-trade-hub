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
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .admin-primary { --tw-primary: 236 91 19; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
    <div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
        <header class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark px-6 py-3 lg:px-10">
            <div class="flex items-center gap-8">
                <a href="{{ route('admin') }}" class="flex items-center gap-4 text-primary">
                    <div class="size-8 flex items-center justify-center bg-primary/10 rounded-lg">
                        <span class="material-symbols-outlined text-primary">dashboard_customize</span>
                    </div>
                    <h2 class="text-slate-900 dark:text-white text-lg font-bold leading-tight tracking-tight">AdminCore</h2>
                </a>
                <label class="hidden md:flex flex-col min-w-40 max-w-64">
                    <div class="flex w-full flex-1 items-stretch rounded-xl h-10 border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <span class="text-slate-400 flex items-center justify-center pl-4 bg-white dark:bg-background-dark">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </span>
                        <input class="flex w-full min-w-0 flex-1 border-none bg-white dark:bg-background-dark focus:outline-0 focus:ring-0 placeholder:text-slate-400 px-4 pl-2 text-sm font-normal" placeholder="Search data..." type="text"/>
                    </div>
                </label>
            </div>
            <div class="flex items-center gap-4">
                <button type="button" class="flex size-10 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-primary/10 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <a href="{{ route('profile.edit') }}" class="flex size-10 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-primary/10 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">settings</span>
                </a>
                <div class="h-10 w-px bg-slate-200 dark:bg-slate-800 mx-2"></div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-bold leading-none">{{ auth()->user()->name ?? auth()->user()->email }}</p>
                        <p class="text-xs text-slate-500 mt-1">Admin</p>
                    </div>
                    <div class="bg-primary/20 aspect-square rounded-full size-10 border-2 border-primary/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">person</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex flex-1 flex-col lg:flex-row">
            @include('partials.sidebar-admin')

            <main class="flex-1 overflow-y-auto p-4 lg:p-8 space-y-8">
                @yield('content')
            </main>
        </div>
    </div>

    @RegisterServiceWorkerScript
</body>
</html>
