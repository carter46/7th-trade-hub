@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'min-h-[72px] flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4']) }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-bold tracking-tight text-text-primary">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-text-secondary">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2 shrink-0">{{ $actions }}</div>
    @endisset
</div>
