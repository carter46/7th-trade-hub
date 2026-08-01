@php
    $icons = app(\App\Services\Branding\PwaBrandingSync::class)->publishedUrls();
    $themeColor = config('pwa.manifest.theme_color', '#0B6A39');
@endphp
<meta name="theme-color" content="{{ $themeColor }}">
<link rel="icon" href="{{ asset('favicon.ico') }}?v={{ $icons['version'] }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ $icons['favicon'] }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $icons['favicon16'] }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $icons['apple'] }}">
<link rel="manifest" href="{{ $icons['manifest'] }}">
@if (! empty($faviconUrl))
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
@endif
