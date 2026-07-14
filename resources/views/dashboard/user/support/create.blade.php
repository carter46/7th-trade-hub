@extends('layouts.dashboard-user')

@section('title', 'New Support Ticket')

@section('content')
<x-layout.page
    title="New Support Ticket"
    subtitle="Describe your issue and we’ll follow up in this thread."
    width="form"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Support', route('dashboard.support.index')],
        ['New ticket', null],
    ]"
>
    <x-ui.card>
        <form method="POST" action="{{ route('dashboard.support.store') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-ui.select label="Category" name="category">
                @foreach ($categories as $c)
                    <option value="{{ $c }}">{{ ucfirst(str_replace('_', ' ', $c)) }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input label="Subject" name="subject" required />
            <x-ui.textarea label="Message" name="body" :rows="5" required />
            <x-ui.button type="submit" icon="support" x-bind:disabled="submitting">Submit</x-ui.button>
        </form>
    </x-ui.card>
</x-layout.page>
@endsection
