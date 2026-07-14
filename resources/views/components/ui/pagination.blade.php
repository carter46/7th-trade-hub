@props([
    'paginator',
])

@if ($paginator && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'pt-4']) }}>
        {{ $paginator->links() }}
    </div>
@endif
