@php
    $ogIcons = app(\App\Services\Branding\PwaBrandingSync::class)->publishedUrls();

    $ogImage = isset($ogImageOverride) ? trim((string) $ogImageOverride) : trim($__env->yieldContent('og_image') ?: '');
    if ($ogImage === '') {
        $ogImage = is_file(public_path('icons/og-image.png'))
            ? $ogIcons['og']
            : (isset($faviconUrl) && is_string($faviconUrl) ? $faviconUrl : '');
    }
    // publishedUrls()/asset() may already be absolute; relative paths need an absolute URL for crawlers.
    if ($ogImage !== '') {
        if (str_starts_with($ogImage, '//')) {
            $ogImage = request()->getScheme().':'.$ogImage;
        } elseif (! str_starts_with($ogImage, 'http://') && ! str_starts_with($ogImage, 'https://')) {
            $ogImage = url($ogImage);
        }
    }

    $ogTitle = isset($ogTitleOverride) ? trim((string) $ogTitleOverride) : trim($__env->yieldContent('og_title') ?: '');
    if ($ogTitle === '') {
        $pageTitle = trim($__env->yieldContent('title') ?: '');
        $ogTitle = $pageTitle !== '' ? ($pageTitle.' | '.config('app.name')) : (string) config('app.name');
    }

    $ogDescription = isset($ogDescriptionOverride) ? trim((string) $ogDescriptionOverride) : trim($__env->yieldContent('og_description') ?: '');
    if ($ogDescription === '') {
        $ogDescription = trim($__env->yieldContent('meta_description') ?: '')
            ?: (string) (config('pwa.manifest.description') ?? config('app.name'));
    }
@endphp
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
@if ($ogImage !== '')
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif
<meta name="twitter:card" content="{{ $ogImage !== '' ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
