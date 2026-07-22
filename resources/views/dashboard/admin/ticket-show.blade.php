@extends('layouts.dashboard-admin')

@section('title', 'Ticket #'.$ticket->id)

@section('content')
<x-layout.page
    :title="$ticket->subject"
    :subtitle="$ticket->category . ' — ' . ($ticket->user?->email ?? '—') . ' — opened ' . $ticket->created_at->format('M j, Y H:i')"
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Support Tickets', route('admin.tickets')],
        ['Ticket', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.tickets')" variant="secondary" size="sm">All tickets</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.card variant="solid">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <x-dashboard.badge :status="$ticket->status === 'open' ? 'pending' : 'completed'">
                {{ $ticket->status }}
            </x-dashboard.badge>
            <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}" class="flex gap-2 items-end" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <x-dashboard.select name="status" size="sm">
                    <option value="open" @selected($ticket->status === 'open')>Open</option>
                    <option value="closed" @selected($ticket->status === 'closed')>Closed</option>
                </x-dashboard.select>
                <x-dashboard.button type="submit" variant="secondary" size="sm" x-bind:disabled="submitting">Update</x-dashboard.button>
            </form>
        </div>
        <div class="text-text-secondary whitespace-pre-wrap text-sm">{{ $ticket->body }}</div>
    </x-dashboard.card>

    @foreach ($ticket->replies as $reply)
        <x-dashboard.card
            variant="solid"
            class="{{ $reply->is_staff ? 'border-primary/30 bg-primary/5' : '' }}"
        >
            <p class="text-xs text-text-muted mb-1">
                {{ $reply->user->name ?? $reply->user->email }}
                @if ($reply->is_staff)
                    <span class="text-primary font-medium">(Staff)</span>
                @endif
                — {{ $reply->created_at->format('M j, H:i') }}
            </p>
            <p class="text-text-secondary text-sm whitespace-pre-wrap">{{ $reply->body }}</p>
        </x-dashboard.card>
    @endforeach

    <x-dashboard.card variant="solid">
        <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.textarea name="body" label="Staff reply" :rows="4" required />
            <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Send reply</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
