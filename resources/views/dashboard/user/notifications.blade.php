@extends('layouts.dashboard-user')

@section('title', 'Notifications')

@section('content')
<x-layout.page title="Notifications" subtitle="Updates on orders, listings, and messages." width="content">
    <x-slot:actions>
        <form method="POST" action="{{ route('dashboard.notifications.read-all') }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-ui.button type="submit" variant="ghost" size="sm" x-bind:disabled="submitting">Mark all read</x-ui.button>
        </form>
    </x-slot:actions>

    <x-ui.card :padding="false">
        @if ($notifications->isEmpty())
            <x-ui.empty
                icon="notifications"
                title="No notifications yet"
                description="Order updates, listing decisions, and message alerts will show up here."
            />
        @else
            <div class="divide-y divide-border-default">
                @foreach ($notifications as $notification)
                    <div class="px-6 py-4 {{ $notification->read_at ? 'opacity-70' : '' }}">
                        @if ($notification->action_url && ! $notification->read_at)
                            <form method="POST" action="{{ route('dashboard.notifications.read', $notification) }}">
                                @csrf
                                <x-ui.button type="submit" variant="ghost" class="!h-auto !w-full !justify-start !px-0 !py-0 text-left whitespace-normal">
                                    <span class="block w-full text-left">
                                        <span class="block text-text-primary font-medium">{{ $notification->title }}</span>
                                        @if ($notification->body)
                                            <span class="block text-text-secondary text-sm mt-1 font-normal">{{ $notification->body }}</span>
                                        @endif
                                        <span class="block text-text-muted text-xs mt-1 font-normal">{{ $notification->created_at->diffForHumans() }}</span>
                                    </span>
                                </x-ui.button>
                            </form>
                        @else
                            <p class="text-text-primary font-medium">{{ $notification->title }}</p>
                            @if ($notification->body)
                                <p class="text-text-secondary text-sm mt-1">{{ $notification->body }}</p>
                            @endif
                            <p class="text-text-muted text-xs mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$notifications" />
    </x-slot:pagination>
</x-layout.page>
@endsection
