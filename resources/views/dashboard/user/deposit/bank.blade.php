@extends('layouts.dashboard-user')

@section('title', 'Deposit Money')

@section('content')
<x-layout.page
    title="Deposit Money"
    subtitle="Bank transfer — upload proof after payment."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Deposit', route('dashboard.deposit.index')],
        ['Bank transfer', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('dashboard.deposit.store-bank') }}" enctype="multipart/form-data" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.input label="Amount (NGN)" type="number" name="amount" min="100" step="0.01" required />
            <x-dashboard.input label="Bank name" name="bank_name" required />
            <x-dashboard.input label="Transfer reference" name="transfer_reference" required />
            <div class="space-y-1.5">
                <label for="proof" class="block text-sm font-medium text-text-secondary">Proof (optional)</label>
                <input
                    id="proof"
                    type="file"
                    name="proof"
                    accept=".jpg,.jpeg,.png,.pdf"
                    class="block w-full text-sm text-text-secondary file:mr-3 file:rounded-lg file:border-0 file:bg-elevated file:px-3 file:py-2 file:text-sm file:text-text-primary"
                />
            </div>
            <x-dashboard.button type="submit" icon="upload" x-bind:disabled="submitting">Submit Deposit</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
