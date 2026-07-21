{{-- Paint SSOT: CSS variables generated from config/dashboard-themes.php --}}
@php
    /** @var \App\Services\ThemeManager $themes */
    $themes = app(\App\Services\ThemeManager::class);
    $nonce = '';
    if (function_exists('csp_nonce')) {
        $nonce = (string) csp_nonce();
    } elseif (class_exists(\Illuminate\Support\Facades\Vite::class)) {
        try {
            $nonce = (string) (\Illuminate\Support\Facades\Vite::cspNonce() ?? '');
        } catch (\Throwable $e) {
            $nonce = '';
        }
    }
@endphp
<style @if ($nonce !== '') nonce="{{ $nonce }}" @endif>
{!! $themes->dashboardThemeStylesheet() !!}
</style>
