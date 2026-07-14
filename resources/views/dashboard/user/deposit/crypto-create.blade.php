@extends('layouts.dashboard-user')

@section('title', 'Sell Crypto')

@section('content')
<x-layout.page
    title="Sell Crypto"
    subtitle="Quote valid for 15 minutes."
    width="form"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Sell Crypto', route('dashboard.crypto-sell.index')],
        ['New quote', null],
    ]"
>
    <x-ui.card>
        <form method="POST" action="{{ route('dashboard.crypto-sell.store') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-ui.select label="Coin" name="coin">
                @foreach ($coins as $c)
                    <option>{{ $c }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input label="Network" name="network" placeholder="e.g. TRC20" />
            <x-ui.input label="Amount" type="number" step="any" name="amount_crypto" required />
            <x-ui.button type="submit" icon="bitcoin" x-bind:disabled="submitting">Get Quote</x-ui.button>
        </form>
    </x-ui.card>
</x-layout.page>
@endsection
