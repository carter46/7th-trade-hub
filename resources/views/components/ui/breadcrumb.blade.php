@props([
    'items' => [],
])

@if (count($items))
    @php
        $lastIndex = count($items) - 1;
    @endphp
    <nav
        {{ $attributes->merge([
            'class' => 'flex min-w-0 max-w-full flex-nowrap items-center gap-1 overflow-hidden text-xs text-text-secondary sm:gap-1.5 sm:text-sm',
            'aria-label' => 'Breadcrumb',
        ]) }}
    >
        @foreach ($items as $i => $item)
            @php
                $label = (string) ($item[0] ?? '');
                $href = $item[1] ?? null;
                $isLast = $i === $lastIndex;
            @endphp
            @if ($i > 0)
                <x-ui.icon name="chevron-right" class="h-3 w-3 shrink-0 text-text-muted sm:h-3.5 sm:w-3.5" aria-hidden="true" />
            @endif
            @if (! empty($href) && ! $isLast)
                <a
                    href="{{ $href }}"
                    title="{{ $label }}"
                    class="min-w-0 shrink truncate hover:text-text-primary transition-colors max-w-[4.25rem] sm:max-w-[7rem] md:max-w-[9rem]"
                >{{ $label }}</a>
            @elseif (! empty($href) && $isLast)
                <a
                    href="{{ $href }}"
                    title="{{ $label }}"
                    class="min-w-0 flex-1 truncate font-medium text-text-primary hover:text-text-primary transition-colors"
                    aria-current="page"
                >{{ $label }}</a>
            @else
                <span
                    class="min-w-0 truncate font-medium text-text-primary {{ $isLast ? 'flex-1' : 'max-w-[4.25rem] sm:max-w-[7rem] md:max-w-[9rem]' }}"
                    title="{{ $label }}"
                    @if ($isLast) aria-current="page" @endif
                >{{ $label }}</span>
            @endif
        @endforeach
    </nav>
@endif
