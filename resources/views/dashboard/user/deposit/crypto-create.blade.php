@extends('layouts.dashboard-user')

@section('title', 'Sell Crypto')

@section('content')
<x-layout.page
    title="Sell Crypto"
    subtitle="Quote valid for 15 minutes."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Sell Crypto', route('dashboard.crypto-sell.index')],
        ['New quote', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('dashboard.crypto-sell.store') }}" class="max-w-form space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.select label="Coin" name="coin">
                @foreach ($coins as $c)
                    <option>{{ $c }}</option>
                @endforeach
            </x-dashboard.select>
            <x-dashboard.input label="Network" name="network" placeholder="e.g. TRC20" />
            <x-dashboard.input label="Amount" type="number" step="any" name="amount_crypto" required />
            <x-dashboard.button type="submit" icon="bitcoin" x-bind:disabled="submitting">Get Quote</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
