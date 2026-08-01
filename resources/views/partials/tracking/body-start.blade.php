@php
    $html = app(\App\Services\Tracking\TrackingScriptRenderer::class)->bodyStartHtml();
@endphp
@if ($html !== '')
{!! $html !!}
@endif
