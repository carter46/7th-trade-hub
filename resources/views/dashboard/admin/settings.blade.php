@extends('layouts.dashboard-admin')

@section('title', 'Platform Settings')

@section('content')
<x-layout.page
    title="Platform Settings"
    subtitle="Configure fees and transaction limits."
    width="form"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Settings', null],
    ]"
>
    <x-ui.card variant="solid">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-ui.input
                name="platform_fee_percent"
                type="number"
                label="Platform fee (%)"
                step="0.01"
                :value="old('platform_fee_percent', $platformFeePercent)"
                hint="Deducted from escrow release to seller."
                required
            />
            <x-ui.input
                name="deposit_min_amount"
                type="number"
                label="Minimum deposit (NGN)"
                :value="old('deposit_min_amount', $depositMinAmount)"
                required
            />
            <x-ui.input
                name="withdrawal_min_amount"
                type="number"
                label="Minimum withdrawal (NGN)"
                :value="old('withdrawal_min_amount', $withdrawalMinAmount)"
                required
            />
            <x-ui.input
                name="withdrawal_max_amount"
                type="number"
                label="Maximum withdrawal (NGN)"
                :value="old('withdrawal_max_amount', $withdrawalMaxAmount)"
                required
            />
            <x-ui.button type="submit" variant="primary" x-bind:disabled="submitting">Save settings</x-ui.button>
        </form>
    </x-ui.card>
</x-layout.page>
@endsection
