@extends('layouts.dashboard-admin')

@section('title', 'Admin Notifications')

@section('content')
<x-layout.page
    title="Admin Notifications"
    subtitle="Platform alerts for administrators."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Notifications', null],
    ]"
>
    <x-slot:actions>
        @if (($unreadCount ?? 0) > 0)
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                @csrf
                <x-dashboard.button type="submit" variant="secondary" size="sm">Mark all read</x-dashboard.button>
            </form>
        @endif
    </x-slot:actions>

    <x-dashboard.table :empty="$notifications->isEmpty()" empty-title="No notifications" striped>
        <x-slot:head>
            <x-dashboard.th>Title</x-dashboard.th>
            <x-dashboard.th>Type</x-dashboard.th>
            <x-dashboard.th>When</x-dashboard.th>
            <x-dashboard.th></x-dashboard.th>
        </x-slot:head>
        @foreach ($notifications as $notification)
            <tr class="{{ $notification->read_at ? '' : 'bg-primary/5' }}">
                <x-dashboard.td>
                    <p class="font-medium text-text-primary">{{ $notification->title }}</p>
                    @if ($notification->body)
                        <p class="text-xs text-text-muted mt-0.5">{{ $notification->body }}</p>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td class="text-xs text-text-muted">{{ $notification->type }}</x-dashboard.td>
                <x-dashboard.td class="text-xs text-text-muted">{{ $notification->created_at->format('M j, Y H:i') }}</x-dashboard.td>
                <x-dashboard.td>
                    @if (! $notification->read_at)
                        <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                            @csrf
                            <x-dashboard.button type="submit" variant="link" size="sm">Mark read</x-dashboard.button>
                        </form>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$notifications" />
    </x-slot:pagination>
</x-layout.page>
@endsection
