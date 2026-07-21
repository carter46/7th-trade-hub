@extends('layouts.dashboard-user')

@section('title', 'Messages')

@section('content')
<x-layout.page title="Messages" subtitle="Your inbox and conversations." width="content">
    <x-slot:actions>
        <x-dashboard.button :href="route('dashboard.messages.create')" icon="plus">New message</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.card :padding="false">
        @if ($messages->isEmpty())
            <x-dashboard.empty
                icon="messages"
                title="No messages yet"
                description="Start a conversation with another user on the platform."
                :action="['href' => route('dashboard.messages.create'), 'label' => 'New message']"
            />
        @else
            <ul class="divide-y divide-border-default">
                @foreach ($messages as $msg)
                    <li>
                        <a href="{{ route('dashboard.messages.show', $msg) }}" class="block px-6 py-4 hover:bg-muted/40 transition-colors">
                            <p class="text-text-primary font-medium {{ $msg->to_user_id === auth()->id() && ! $msg->read_at ? 'font-bold' : '' }}">{{ $msg->subject }}</p>
                            <p class="text-text-muted text-xs mt-1">
                                {{ $msg->from_user_id === auth()->id() ? 'To' : 'From' }}:
                                {{ $msg->from_user_id === auth()->id() ? $msg->toUser?->email : $msg->fromUser?->email }}
                                — {{ $msg->created_at->diffForHumans() }}
                            </p>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-dashboard.card>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$messages" />
    </x-slot:pagination>
</x-layout.page>
@endsection
