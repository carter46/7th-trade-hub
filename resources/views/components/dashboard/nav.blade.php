@props([
    'role',
    'user' => null,
    'label' => 'Primary navigation',
])

@php
    $user = $user ?? auth()->user();
    $entries = \App\Support\DashboardNavigation::for($role, $user);
    $searchIndex = \App\Support\DashboardNavigation::searchIndex($role, $user);
    $initiallyOpen = \App\Support\DashboardNavigation::initiallyOpenGroups($entries);
    $userId = $user?->getAuthIdentifier() ?? 'guest';
    $storageKey = "7th.dashboard.nav.{$role}.{$userId}";
    $idPrefix = "dashboard-nav-{$role}";
    $sidebarOptions = \Illuminate\Support\Js::from([
        'storageKey' => $storageKey,
        'initiallyOpen' => $initiallyOpen,
        'destinations' => $searchIndex,
    ]);
@endphp

<div
    class="flex min-h-0 flex-1 flex-col gap-3"
    x-data="sidebarNav({{ $sidebarOptions }})"
    data-dashboard-nav="{{ $role }}"
>
    <x-dashboard.nav-search />

    <x-dashboard.sidebar
        :label="$label"
        class="flex min-h-0 flex-1 flex-col gap-1.5 overflow-y-auto overscroll-contain px-1 scrollbar-hide"
        x-show="!query.trim()"
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

    <div
        class="flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto overscroll-contain px-1 scrollbar-hide"
        x-show="query.trim()"
        x-cloak
        role="listbox"
        :aria-label="'Search results'"
    >
        <template x-if="filteredDestinations().length === 0">
            <p class="px-3 py-4 text-sm text-text-muted">No matching pages.</p>
        </template>
        <template x-for="(item, index) in filteredDestinations()" :key="item.id">
            <a
                :href="item.url"
                class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 text-sm transition-colors focus-ring"
                :class="index === activeResult ? 'bg-primary/10 text-primary' : 'text-text-secondary hover:bg-muted/60 hover:text-text-primary'"
                role="option"
                :aria-selected="(index === activeResult).toString()"
                @click="close()"
                @mouseenter="activeResult = index"
            >
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-medium" x-text="item.label"></span>
                    <span class="block truncate text-[11px] text-text-muted" x-text="item.group || ''" x-show="item.group"></span>
                </span>
            </a>
        </template>
    </div>
</div>
