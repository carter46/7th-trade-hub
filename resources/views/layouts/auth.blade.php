<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '7th Trade Hub - Authentication')</title>
    @PwaHead

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative isolate min-h-screen flex items-center justify-center bg-[#0F172A] font-sans text-text-primary antialiased overflow-x-hidden p-4 sm:p-8">
    <div
        class="pointer-events-none absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('{{ asset('assets/images/Image_ro410gro410gro41.png') }}')"
        aria-hidden="true"
    ></div>
    <div
        class="pointer-events-none absolute inset-0 z-[1]"
        style="background: linear-gradient(165deg, rgba(15, 23, 42, 0.98) 0%, rgba(15, 23, 42, 0.95) 50%, rgba(15, 23, 42, 0.98) 100%), rgba(15, 23, 42, 0.85);"
        aria-hidden="true"
    ></div>
    <div
        class="pointer-events-none absolute inset-0 z-[1]"
        aria-hidden="true"
        style="background-image: radial-gradient(circle at top right, rgba(22, 163, 74, 0.08), transparent 55%), radial-gradient(circle at bottom left, rgba(22, 163, 74, 0.04), transparent 50%);"
    ></div>

    <div class="relative z-10 w-full">
        @yield('content')
    </div>

    <div class="relative z-10">
        <x-ui.toast />
    </div>
    @RegisterServiceWorkerScript
</body>
</html>
