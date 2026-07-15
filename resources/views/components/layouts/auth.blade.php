<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? '7th Trade Hub - Authentication' }}</title>
    @PwaHead

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative p-4 sm:p-8 min-h-screen flex items-center justify-center bg-surface font-sans text-text-primary antialiased overflow-x-hidden">
    {{-- Photo sits under dark overlay — keep subtle --}}
    <div class="pointer-events-none absolute inset-0 -z-20" aria-hidden="true">
        <img
            src="{{ asset('assets/images/Image_ro410gro410gro41.png') }}"
            alt=""
            class="h-full w-full object-cover opacity-[0.22]"
        >
    </div>
    <div class="pointer-events-none absolute inset-0 -z-10 bg-[#0F172A]/90" aria-hidden="true"></div>
    <div
        class="pointer-events-none absolute inset-0 -z-10"
        aria-hidden="true"
        style="background-image: radial-gradient(circle at top right, rgba(22, 163, 74, 0.12), transparent), radial-gradient(circle at bottom left, rgba(22, 163, 74, 0.06), transparent);"
    ></div>

    {{ $slot }}

    <x-ui.toast />
    @RegisterServiceWorkerScript
</body>
</html>
