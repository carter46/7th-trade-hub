@props([
    'title' => null,
    'subtitle' => null,
])
<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div>
        @if ($title)
            <h1 class="text-2xl font-bold text-text-primary tracking-tight">{{ $title }}</h1>
        @endif
        @if ($subtitle)
            <p class="mt-1 text-sm text-text-secondary">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
