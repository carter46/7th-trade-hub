@extends('layouts.dashboard-user')

@section('title', 'New Message')

@section('content')
<x-layout.page
    title="New Message"
    subtitle="Send a private message to another user."
    width="form"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Messages', route('dashboard.messages')],
        ['New', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('dashboard.messages.store') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.input label="Recipient email" type="email" name="to_email" :value="old('to_email')" required />
            <x-dashboard.input label="Subject" name="subject" :value="old('subject')" required />
            <x-dashboard.textarea label="Message" name="body" :rows="5" required>{{ old('body') }}</x-dashboard.textarea>
            <x-dashboard.button type="submit" icon="messages" x-bind:disabled="submitting">Send</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
