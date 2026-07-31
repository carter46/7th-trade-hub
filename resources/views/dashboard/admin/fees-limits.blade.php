@extends('layouts.dashboard-admin')

@section('title', 'Fees & Limits')

@section('content')
<x-layout.page
    title="Fees & Limits"
    subtitle="Operational fee and amount limits for deposits, withdrawals, and escrow releases."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Fees & Limits', null],
    ]"
>
    <x-dashboard.card variant="solid">
        <form method="POST" action="{{ route('admin.fees-limits.update') }}" class="space-y-4 max-w-xl" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.input
                name="platform_fee_percent"
                type="number"
                label="Platform fee (%)"
                step="0.01"
                :value="old('platform_fee_percent', $platformFeePercent)"
                hint="Deducted from escrow release to seller."
                required
            />
            <x-dashboard.input
                name="deposit_min_amount"
                type="number"
                label="Minimum deposit (NGN)"
                :value="old('deposit_min_amount', $depositMinAmount)"
                required
            />
            <x-dashboard.input
                name="withdrawal_min_amount"
                type="number"
                label="Minimum withdrawal (NGN)"
                :value="old('withdrawal_min_amount', $withdrawalMinAmount)"
                required
            />
            <x-dashboard.input
                name="withdrawal_max_amount"
                type="number"
                label="Maximum withdrawal (NGN)"
                :value="old('withdrawal_max_amount', $withdrawalMaxAmount)"
                required
            />
            <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save fees & limits</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
