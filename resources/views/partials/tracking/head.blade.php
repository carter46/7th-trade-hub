@php
    $html = app(\App\Services\Tracking\TrackingScriptRenderer::class)->headHtml();
@endphp
@if ($html !== '')
{!! $html !!}
@endif
