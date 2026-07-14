@props([
    'icon' => 'empty',
    'title' => 'Nothing here yet',
    'description' => null,
    'action' => null,
    'secondary' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center py-12 px-6 min-h-[240px]']) }}>
    <div class="w-12 h-12 rounded-2xl bg-muted/50 text-text-secondary flex items-center justify-center mb-4">
        <x-ui.icon :name="$icon" class="w-7 h-7" />
    </div>
    <h3 class="text-lg font-semibold text-text-primary">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 text-sm text-text-secondary max-w-md">{{ $description }}</p>
    @endif
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
</div>
