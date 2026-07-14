@props([
    'items' => [],
])

@if (count($items))
    <nav {{ $attributes->merge(['class' => 'flex items-center gap-2 text-sm text-text-secondary', 'aria-label' => 'Breadcrumb']) }}>
        @foreach ($items as $i => $item)
            @if ($i > 0)
                <x-ui.icon name="chevron-right" class="w-4 h-4 text-text-muted" />
            @endif
            @if (! empty($item[1]))
                <a href="{{ $item[1] }}" class="hover:text-text-primary transition-colors">{{ $item[0] }}</a>
            @else
                <span class="text-text-primary font-medium" aria-current="page">{{ $item[0] }}</span>
            @endif
        @endforeach
    </nav>
@endif
