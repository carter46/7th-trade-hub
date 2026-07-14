@extends('layouts.dashboard-user')

@section('title', 'KYC Verification')

@section('content')
<x-layout.page
    title="KYC Verification"
    subtitle="Level 1 is required to create your wallet."
    width="form"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['KYC', null],
    ]"
>
    <x-ui.card>
        <p class="text-text-primary">Current level: <strong>{{ $kycLevel }}</strong></p>

        @if ($kycLevel < 1)
            <form method="POST" action="{{ route('dashboard.kyc.store') }}" class="mt-6 space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <x-ui.input label="Document type" name="document_type" required />
                <x-ui.input label="Document number" name="document_number" required />
                <x-ui.button type="submit" icon="kyc" x-bind:disabled="submitting">Submit KYC Level 1</x-ui.button>
            </form>
        @else
            <x-ui.alert type="success" title="KYC approved" class="mt-4">
                You can <a href="{{ route('dashboard.wallet') }}" class="underline font-medium">create your wallet</a>.
            </x-ui.alert>
        @endif

        @if ($submission)
            <p class="text-text-secondary mt-4 text-sm">Latest submission: {{ $submission->status }}</p>
        @endif
    </x-ui.card>
</x-layout.page>
@endsection
