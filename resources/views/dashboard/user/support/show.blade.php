@extends('layouts.dashboard-user')

@section('title', $ticket->subject)

@section('content')
<x-layout.page
    title="{{ $ticket->subject }}"
    subtitle="{{ $ticket->category }} — {{ $ticket->status }}"
    width="content-md"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Support', route('dashboard.support.index')],
        ['Ticket', null],
    ]"
>
    <x-slot:actions>
        <x-ui.badge :status="$ticket->status === 'open' ? 'pending' : 'completed'">{{ $ticket->status }}</x-ui.badge>
    </x-slot:actions>

    <x-ui.card>
        <div class="text-text-primary whitespace-pre-wrap">{{ $ticket->body }}</div>
    </x-ui.card>

    @foreach ($ticket->replies as $reply)
        <x-ui.card :class="$reply->is_staff ? 'border border-primary/30' : ''">
            <p class="text-xs text-text-muted mb-2">
                {{ $reply->user->name }}
                @if ($reply->is_staff)
                    <span class="text-primary font-medium">(Staff)</span>
                @endif
            </p>
            <p class="text-sm text-text-primary whitespace-pre-wrap">{{ $reply->body }}</p>
        </x-ui.card>
    @endforeach

    <x-ui.card>
        <form method="POST" action="{{ route('dashboard.support.reply', $ticket) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-ui.textarea label="Reply" name="body" :rows="3" required />
            <x-ui.button type="submit" size="sm" icon="chat" x-bind:disabled="submitting">Reply</x-ui.button>
        </form>
    </x-ui.card>
</x-layout.page>
@endsection
