@props([
    'listing',
    'mode' => 'menu', // menu | buttons
])

@php
    $isMenu = $mode === 'menu';
@endphp

@if (in_array($listing->status, ['draft', 'rejected'], true))
    @if ($isMenu)
        <x-dashboard.row-actions>
            <x-dashboard.menu-item :href="route('dashboard.listings.edit', $listing)">Edit</x-dashboard.menu-item>
            <form method="POST" action="{{ route('dashboard.listings.submit', $listing) }}">
                @csrf
                <x-dashboard.menu-item type="submit">Submit</x-dashboard.menu-item>
            </form>
            <form
                method="POST"
                action="{{ route('dashboard.listings.destroy', $listing) }}"
                onsubmit="return confirm('Delete this listing? You can ask support if you remove it by mistake.');"
            >
                @csrf
                @method('DELETE')
                <x-dashboard.menu-item type="submit" variant="danger">Delete</x-dashboard.menu-item>
            </form>
        </x-dashboard.row-actions>
    @else
        <div class="flex flex-wrap gap-2">
            <x-dashboard.button :href="route('dashboard.listings.edit', $listing)" variant="secondary" size="sm">Edit</x-dashboard.button>
            <form method="POST" action="{{ route('dashboard.listings.submit', $listing) }}">
                @csrf
                <x-dashboard.button type="submit" size="sm">Submit</x-dashboard.button>
            </form>
            <form
                method="POST"
                action="{{ route('dashboard.listings.destroy', $listing) }}"
                onsubmit="return confirm('Delete this listing? You can ask support if you remove it by mistake.');"
            >
                @csrf
                @method('DELETE')
                <x-dashboard.button type="submit" variant="danger" size="sm">Delete</x-dashboard.button>
            </form>
        </div>
    @endif
@elseif ($listing->status === 'published')
    @if ($isMenu)
        <x-dashboard.row-actions>
            <x-dashboard.menu-item :href="route('dashboard.marketplace.show', $listing->slug)">View live</x-dashboard.menu-item>
            <form method="POST" action="{{ route('dashboard.listings.revision', $listing) }}">
                @csrf
                <x-dashboard.menu-item type="submit">New revision</x-dashboard.menu-item>
            </form>
            <form method="POST" action="{{ route('dashboard.listings.archive', $listing) }}">
                @csrf
                <x-dashboard.menu-item type="submit" variant="danger">Archive</x-dashboard.menu-item>
            </form>
        </x-dashboard.row-actions>
    @else
        <div class="flex flex-wrap gap-2">
            <x-dashboard.button :href="route('dashboard.marketplace.show', $listing->slug)" variant="secondary" size="sm">View live</x-dashboard.button>
            <form method="POST" action="{{ route('dashboard.listings.revision', $listing) }}">
                @csrf
                <x-dashboard.button type="submit" size="sm">New revision</x-dashboard.button>
            </form>
            <form method="POST" action="{{ route('dashboard.listings.archive', $listing) }}">
                @csrf
                <x-dashboard.button type="submit" variant="danger" size="sm">Archive</x-dashboard.button>
            </form>
        </div>
    @endif
@elseif ($listing->status === 'archived')
    @if ($isMenu)
        <x-dashboard.row-actions>
            <form method="POST" action="{{ route('dashboard.listings.restore-archive', $listing) }}">
                @csrf
                <x-dashboard.menu-item type="submit" variant="success">Restore to draft</x-dashboard.menu-item>
            </form>
        </x-dashboard.row-actions>
    @else
        <form method="POST" action="{{ route('dashboard.listings.restore-archive', $listing) }}">
            @csrf
            <x-dashboard.button type="submit" variant="success" size="sm">Restore to draft</x-dashboard.button>
        </form>
    @endif
@elseif ($listing->status === 'suspended')
    @if ($isMenu)
        <x-dashboard.row-actions>
            <form method="POST" action="{{ route('dashboard.listings.archive', $listing) }}">
                @csrf
                <x-dashboard.menu-item type="submit" variant="danger">Archive</x-dashboard.menu-item>
            </form>
        </x-dashboard.row-actions>
    @else
        <form method="POST" action="{{ route('dashboard.listings.archive', $listing) }}">
            @csrf
            <x-dashboard.button type="submit" variant="danger" size="sm">Archive</x-dashboard.button>
        </form>
    @endif
@else
    <span class="text-text-muted text-xs">Awaiting admin</span>
@endif
