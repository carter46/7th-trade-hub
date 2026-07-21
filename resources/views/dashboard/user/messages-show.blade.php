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
        <x-dashboard.button :href="route('dashboard.messages')" variant="ghost" size="sm">← Inbox</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.card>
        <div class="text-text-primary whitespace-pre-wrap">{{ $message->body }}</div>
    </x-dashboard.card>

    <x-dashboard.card>
        <form method="POST" action="{{ route('dashboard.messages.reply', $message) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.textarea label="Reply" name="body" :rows="4" placeholder="Reply..." required />
            <x-dashboard.button type="submit" size="sm" icon="chat" x-bind:disabled="submitting">Reply</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
