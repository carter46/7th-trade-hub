@props([
    'icon' => 'empty',
    'title' => 'Nothing here yet',
    'description' => null,
    'action' => null,
    'secondary' => null,
])

{{-- Internals: prefer x-dashboard.empty-state on authenticated pages --}}
<x-dashboard.empty-state
    :icon="$icon"
    :title="$title"
    :description="$description"
    {{ $attributes }}
>
    @if ($action || $secondary)
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            @if ($action)
                <x-ui.button :href="$action['href'] ?? '#'" variant="primary">
                    {{ $action['label'] ?? 'Continue' }}
                </x-ui.button>
            @endif
            @if ($secondary)
                <x-ui.button :href="$secondary['href'] ?? '#'" variant="ghost">
                    {{ $secondary['label'] ?? 'Learn more' }}
                </x-ui.button>
            @endif
        </div>
    @endif
    {{ $slot }}
</x-dashboard.empty-state>
