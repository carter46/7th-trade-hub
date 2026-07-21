@props([
    'paginator',
])

<x-ui.pagination :paginator="$paginator" {{ $attributes }} />
