@php
    $icons = app(\App\Services\Branding\PwaBrandingSync::class)->publishedUrls();
    $themeColor = config('pwa.manifest.theme_color', '#0B6A39');
@endphp
<meta name="theme-color" content="{{ $themeColor }}">
{{-- Prefer sized PNGs from branding sync. Do not advertise favicon.ico here — browsers still
     request /favicon.ico by convention, and an unsized ICO link often beats PNG in Chrome. --}}
<link rel="icon" type="image/png" sizes="32x32" href="{{ $icons['favicon'] }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $icons['favicon16'] }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $icons['apple'] }}">
<link rel="manifest" href="{{ $icons['manifest'] }}">
