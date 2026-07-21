@extends('layouts.dashboard-user')

@section('title', 'Withdraw')

@section('content')
<x-layout.page
    title="Withdraw to Bank"
    subtitle="Funds are locked until your request is processed."
    width="content-md"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Withdraw', route('dashboard.withdrawal.index')],
        ['Request', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('dashboard.withdrawal.store') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.input label="Amount (NGN)" type="number" name="amount" min="100" required />
            <x-dashboard.input label="Bank name" name="bank_name" required />
            <x-dashboard.input label="Account number" name="account_number" required />
            <x-dashboard.input label="Account name" name="account_name" required />
            <x-dashboard.button type="submit" icon="withdraw" x-bind:disabled="submitting">Request Withdrawal</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
