@props([
    'role',
    'user' => null,
    'label' => 'Primary navigation',
])

@php
    $user = $user ?? auth()->user();
    $entries = \App\Support\DashboardNavigation::for($role, $user);
    $initiallyOpen = \App\Support\DashboardNavigation::initiallyOpenGroups($entries);
    $userId = $user?->getAuthIdentifier() ?? 'guest';
    $storageKey = "7th.dashboard.nav.{$role}.{$userId}";
    $idPrefix = "dashboard-nav-{$role}";
    $sidebarOptions = \Illuminate\Support\Js::from([
        'storageKey' => $storageKey,
        'initiallyOpen' => $initiallyOpen,
    ]);
@endphp

<x-dashboard.sidebar
    :label="$label"
    class="flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto overscroll-contain px-1 scrollbar-hide"
    x-data="sidebarNav({{ $sidebarOptions }})"
    data-dashboard-nav="{{ $role }}"
>
    @foreach ($entries as $entry)
        @if (($entry['type'] ?? 'link') === 'group')
            @php($groupActive = \App\Support\DashboardNavigation::groupIsActive($entry))
            <x-dashboard.nav-group
                :group="$entry"
                :active="$groupActive"
                :expanded="in_array((string) $entry['id'], $initiallyOpen, true)"
                :id-prefix="$idPrefix"
            />
        @else
            <x-dashboard.nav-item
                :item="$entry"
                :active="\App\Support\DashboardNavigation::isActive($entry)"
            />
        @endif
    @endforeach
</x-dashboard.sidebar>
