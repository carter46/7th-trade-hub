@props([
    'icon' => 'empty',
    'title' => 'Nothing here yet',
    'description' => null,
    'action' => null,
    'assetKey' => null,
])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center py-12 px-6']) }}>
    @if ($assetKey)
        <x-dashboard.asset :key="$assetKey" class="h-28 w-auto mb-4 opacity-90" :alt="$title" />
    @else
        <div class="size-14 rounded-2xl flex items-center justify-center mb-4" style="background: var(--th-icon-bg); color: var(--th-icon-fg);">
            <x-ui.icon :name="$icon" class="w-7 h-7" />
        </div>
    @endif
    <h3 class="text-base font-semibold text-text-primary">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-md text-sm text-text-secondary">{{ $description }}</p>
    @endif
    @if ($action)
        <div class="mt-4">{{ $action }}</div>
    @endif
    {{ $slot }}
</div>
