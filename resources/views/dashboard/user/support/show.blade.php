@extends('layouts.dashboard-user')

@section('title', $ticket->subject)

@section('content')
<x-layout.page
    title="{{ $ticket->subject }}"
    subtitle="{{ $ticket->category }} — {{ $ticket->status }}"
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Support', route('dashboard.support.index')],
        ['Ticket', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.badge :status="$ticket->status === 'open' ? 'pending' : 'completed'">{{ $ticket->status }}</x-dashboard.badge>
    </x-slot:actions>

    <x-dashboard.card>
        <div class="text-text-primary whitespace-pre-wrap">{{ $ticket->body }}</div>
    </x-dashboard.card>

    @foreach ($ticket->replies as $reply)
        <x-dashboard.card :class="$reply->is_staff ? 'border border-primary/30' : ''">
            <p class="text-xs text-text-muted mb-2">
                {{ $reply->user->name }}
                @if ($reply->is_staff)
                    <span class="text-primary font-medium">(Staff)</span>
                @endif
            </p>
            <p class="text-sm text-text-primary whitespace-pre-wrap">{{ $reply->body }}</p>
        </x-dashboard.card>
    @endforeach

    <x-dashboard.card>
        <form method="POST" action="{{ route('dashboard.support.reply', $ticket) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.textarea label="Reply" name="body" :rows="3" required />
            <x-dashboard.button type="submit" size="sm" icon="chat" x-bind:disabled="submitting">Reply</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
