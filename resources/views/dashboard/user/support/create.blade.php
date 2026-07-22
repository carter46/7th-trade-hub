@extends('layouts.dashboard-user')

@section('title', 'New Support Ticket')

@section('content')
<x-layout.page
    title="New Support Ticket"
    subtitle="Describe your issue and we’ll follow up in this thread."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Support', route('dashboard.support.index')],
        ['New ticket', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('dashboard.support.store') }}" class="max-w-form space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.select label="Category" name="category">
                @foreach ($categories as $c)
                    <option value="{{ $c }}">{{ ucfirst(str_replace('_', ' ', $c)) }}</option>
                @endforeach
            </x-dashboard.select>
            <x-dashboard.input label="Subject" name="subject" required />
            <x-dashboard.textarea label="Message" name="body" :rows="5" required />
            <x-dashboard.button type="submit" icon="support" x-bind:disabled="submitting">Submit</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
