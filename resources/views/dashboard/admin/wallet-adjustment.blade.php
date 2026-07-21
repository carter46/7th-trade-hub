@extends('layouts.dashboard-admin')

@section('title', 'Wallet Adjustment')

@section('content')
<x-layout.page
    title="Wallet Adjustment"
    subtitle="Credit or debit a user wallet with a recorded reason."
    width="content-md"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Wallet Adjust', null],
    ]"
>
    <x-dashboard.card variant="solid">
        <form
            method="POST"
            action="{{ route('admin.wallet-adjustment.store') }}"
            class="space-y-4"
            x-data="{ pending: false }"
            @submit="if (!pending) { $event.preventDefault(); $dispatch('open-modal', 'confirm-wallet-adjust') }"
            @modal-confirmed.window="if ($event.detail === 'confirm-wallet-adjust') { pending = true; $el.submit() }"
        >
            @csrf
            <x-dashboard.input
                name="user_email"
                type="email"
                label="User email"
                :value="old('user_email')"
                required
            />
            <x-dashboard.input
                name="amount"
                type="number"
                label="Amount (NGN)"
                step="0.01"
                :value="old('amount')"
                hint="Use negative values to debit the wallet."
                required
            />
            <x-dashboard.textarea name="reason" label="Reason" :rows="3" required>{{ old('reason') }}</x-dashboard.textarea>
            <x-dashboard.button type="submit" variant="primary">Apply adjustment</x-dashboard.button>
        </form>
    </x-dashboard.card>

    <x-dashboard.modal
        name="confirm-wallet-adjust"
        title="Apply this wallet adjustment?"
        variant="warning"
        confirm-label="Apply"
    >
        This will credit or debit the user wallet and create an audit record.
    </x-dashboard.modal>
</x-layout.page>
@endsection
