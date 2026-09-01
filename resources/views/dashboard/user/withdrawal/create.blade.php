@extends('layouts.dashboard-user')

@section('title', 'Withdraw')

@section('content')
<x-layout.page
    title="Withdraw to Bank"
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Withdraw', route('dashboard.withdrawal.index')],
        ['Request', null],
    ]"
>
    <x-dashboard.card>
        <div class="mb-4 rounded-xl border border-border-subtle p-4 text-sm">
            <p class="font-medium text-text-primary">Payout bank</p>
            <p class="mt-1 text-text-secondary">{{ $bank->bank_name }} · {{ $bank->maskedAccountNumber() }}</p>
            <p class="text-text-muted">{{ $bank->verified_name }}</p>
            <a href="{{ route('dashboard.banks.index') }}" class="mt-2 inline-block text-sm underline">Manage bank</a>
        </div>
        <p class="mb-4 text-sm text-text-secondary">Available: ₦{{ number_format($wallet->availableBalance(), 2) }}</p>
        <form method="POST" action="{{ route('dashboard.withdrawal.store') }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <input type="hidden" name="user_bank_account_id" value="{{ $bank->id }}">
            <x-dashboard.input label="Amount (NGN)" type="number" name="amount" min="100" step="0.01" required />
            <x-dashboard.button type="submit" icon="withdraw" x-bind:disabled="submitting">Request Withdrawal</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
