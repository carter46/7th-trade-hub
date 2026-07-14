@extends('layouts.dashboard-user')

@section('title', $message->subject)

@section('content')
<x-layout.page
    title="{{ $message->subject }}"
    subtitle="From {{ $message->fromUser?->name ?? $message->fromUser?->email }} to {{ $message->toUser?->name ?? $message->toUser?->email }} — {{ $message->created_at->format('M j, Y H:i') }}"
    width="content-md"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Messages', route('dashboard.messages')],
        ['Thread', null],
    ]"
>
    <x-slot:actions>
        <x-ui.button :href="route('dashboard.messages')" variant="ghost" size="sm">← Inbox</x-ui.button>
    </x-slot:actions>

    <x-ui.card>
        <div class="text-text-primary whitespace-pre-wrap">{{ $message->body }}</div>
    </x-ui.card>

    <x-ui.card>
        <form method="POST" action="{{ route('dashboard.messages.reply', $message) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-ui.textarea label="Reply" name="body" :rows="4" placeholder="Reply..." required />
            <x-ui.button type="submit" size="sm" icon="chat" x-bind:disabled="submitting">Reply</x-ui.button>
        </form>
    </x-ui.card>
</x-layout.page>
@endsection
