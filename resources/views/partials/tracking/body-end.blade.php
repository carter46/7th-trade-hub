@php
    $html = app(\App\Services\Tracking\TrackingScriptRenderer::class)->bodyEndHtml();
@endphp
@if ($html !== '')
{!! $html !!}
@endif
