@php
    $items = [
        'profile' => ['label' => 'Profile', 'icon' => 'user'],
        'security' => ['label' => 'Security', 'icon' => 'lock'],
        'notifications' => ['label' => 'Notifications', 'icon' => 'notifications'],
        'preferences' => ['label' => 'Preferences', 'icon' => 'tune'],
        'sessions' => ['label' => 'Sessions', 'icon' => 'monitoring'],
    ];
@endphp

<nav class="flex gap-2 overflow-x-auto pb-1" aria-label="Account settings">
    @foreach ($items as $key => $item)
        <a
            href="{{ route($prefix.'.account.'.$key) }}"
            @class([
                'inline-flex min-h-11 shrink-0 items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-colors focus-ring',
                'border-primary bg-primary/10 text-primary' => request()->routeIs($prefix.'.account.'.$key),
                'border-border-default bg-elevated text-text-secondary hover:text-text-primary' => ! request()->routeIs($prefix.'.account.'.$key),
            ])
        >
            <x-ui.icon :name="$item['icon']" class="h-4 w-4" />
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
