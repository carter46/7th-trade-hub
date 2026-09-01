@props([
    'title' => null,
    'subtitle' => null,
])
<div {{ $attributes->merge(['class' => 'space-y-3']) }}>
    <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        @if ($title)
            <h1 class="min-w-0 text-xl font-bold tracking-tight text-text-primary break-words sm:text-2xl">{{ $title }}</h1>
        @endif
        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>
    @if ($subtitle)
        <p class="max-w-3xl text-sm leading-relaxed text-text-secondary">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
</div>
