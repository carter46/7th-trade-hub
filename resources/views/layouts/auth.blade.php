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
    <style>
        .auth-body {
            background-color: #0F172A;
            background-image: radial-gradient(circle at top right, rgba(22, 163, 74, 0.1), transparent),
                radial-gradient(circle at bottom left, rgba(22, 163, 74, 0.05), transparent);
        }
    </style>
</head>
<body class="p-4 sm:p-8 auth-body min-h-screen flex items-center justify-center bg-surface font-sans text-text-primary antialiased">
    @yield('content')

    <x-ui.toast />
    @RegisterServiceWorkerScript
</body>
</html>
