@php
    $items = [
        'profile' => ['label' => 'Profile', 'icon' => 'user'],
        'security' => ['label' => 'Security', 'icon' => 'lock'],
        'notifications' => ['label' => 'Notifications', 'icon' => 'notifications'],
        'preferences' => ['label' => 'Preferences', 'icon' => 'tune'],
        'sessions' => ['label' => 'Sessions', 'icon' => 'monitoring'],
    ];

    $active = collect($items)->keys()->first(fn ($key) => request()->routeIs($prefix.'.account.'.$key)) ?? 'profile';

    $tabs = collect($items)->map(fn ($item, $key) => [
        'id' => $key,
        'label' => $item['label'],
        'href' => route($prefix.'.account.'.$key),
    ])->values()->all();
@endphp

<nav data-account-menu aria-label="Account settings">
    <x-dashboard.ajax-tabs
        variant="pills"
        :active="$active"
        :tabs="$tabs"
    />
</nav>
