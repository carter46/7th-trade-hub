@props([
    'tabs' => [],
    'active' => null,
])

@php
    /** @var array<int, array{label: string, href: string, id?: string, count?: int|null}> $tabs */
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap gap-1 border-b border-border-default']) }} role="tablist">
    @foreach ($tabs as $tab)
        @php
            $id = $tab['id'] ?? \Illuminate\Support\Str::slug($tab['label']);
            $isActive = ($active ?? null) === $id || (($active ?? null) === null && $loop->first);
        @endphp
        <a
            href="{{ $tab['href'] }}"
            role="tab"
            @if ($isActive) aria-current="page" @endif
            class="inline-flex min-h-11 items-center gap-2 border-b-2 px-4 py-2 text-sm font-medium transition-colors focus-ring {{ $isActive ? 'border-primary text-primary' : 'border-transparent text-text-secondary hover:text-text-primary' }}"
        >
            <span>{{ $tab['label'] }}</span>
            @if (isset($tab['count']))
                <span class="rounded-full bg-muted px-2 py-0.5 text-[11px] text-text-muted">{{ $tab['count'] }}</span>
            @endif
        </a>
    @endforeach
</div>
