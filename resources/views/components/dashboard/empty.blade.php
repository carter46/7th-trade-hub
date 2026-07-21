@props([
    'icon' => 'empty',
    'title' => 'Nothing here yet',
    'description' => null,
    'action' => null,
    'secondary' => null,
])

<x-dashboard.empty-state :icon="$icon" :title="$title" :description="$description" {{ $attributes }}>
    @if ($action || $secondary)
        <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
            @if ($action)
                <x-dashboard.button :href="$action['href'] ?? '#'" variant="primary">
                    {{ $action['label'] ?? 'Continue' }}
                </x-dashboard.button>
            @endif
            @if ($secondary)
                <x-dashboard.button :href="$secondary['href'] ?? '#'" variant="ghost">
                    {{ $secondary['label'] ?? 'Learn more' }}
                </x-dashboard.button>
            @endif
        </div>
    @endif
    {{ $slot }}
</x-dashboard.empty-state>
