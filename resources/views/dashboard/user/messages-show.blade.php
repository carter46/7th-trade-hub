@extends('layouts.dashboard-user')

@section('title', 'Escrow — '.$order->reference)

@section('content')
@php
    $ordersHref = ($isSeller ?? false) ? route('dashboard.sales') : route('dashboard.orders');
    $ordersLabel = ($isSeller ?? false) ? 'Sales' : 'Orders';
@endphp
<x-layout.page
    title="Order {{ $order->reference }}"
    :subtitle="($order->listing?->title ?? 'Listing unavailable').' · '.($counterpart?->name ?? '—')"
    width="default"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Escrow Conversations', route('dashboard.messages')],
        [$order->reference, null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.badge :status="$order->escrow?->status ?? 'default'">{{ $order->escrow?->status ?? '—' }}</x-dashboard.badge>
        <x-dashboard.button :href="$ordersHref" variant="secondary" size="sm">{{ $ordersLabel }}</x-dashboard.button>
    </x-slot:actions>

    <div class="space-y-4">
        @forelse ($messages as $message)
            <x-dashboard.card :class="(int) $message->from_user_id === (int) auth()->id() ? 'border border-primary/20' : ''">
                <p class="text-xs text-text-muted mb-2">
                    {{ $message->fromUser?->name ?? 'User' }}
                    · {{ $message->created_at->format('M j, Y H:i') }}
                </p>
                <p class="text-sm text-text-primary whitespace-pre-wrap">{{ $message->body }}</p>
            </x-dashboard.card>
        @empty
            <x-dashboard.alert type="info">No messages yet. Say hello to start the conversation.</x-dashboard.alert>
        @endforelse

        @if ($canReply && $counterpart)
            <x-dashboard.card>
                <form method="POST" action="{{ route('dashboard.messages.order.reply', $order) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <x-dashboard.textarea label="Message" name="body" :rows="4" required />
                    <x-dashboard.button type="submit" size="sm" icon="chat" x-bind:disabled="submitting">Send</x-dashboard.button>
                </form>
            </x-dashboard.card>
        @elseif ($canReply && ! $counterpart)
            <x-dashboard.alert type="warning">
                The other party is unavailable, so new replies cannot be sent.
            </x-dashboard.alert>
        @else
            <x-dashboard.alert type="warning">
                This escrow conversation is closed. You can still read the history above.
            </x-dashboard.alert>
        @endif
    </div>
</x-layout.page>
@endsection
