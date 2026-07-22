@extends('layouts.dashboard-admin')

@section('title', 'Edit Exchange Rate')

@section('content')
<x-layout.page
    title="Edit Exchange Rate"
    subtitle="Update buy and sell rates for {{ $rate->asset }}."
    width="form"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Exchange Rates', route('admin.exchange-rates')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.exchange-rates.update', $rate) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            @include('dashboard.admin.exchange-rates._form', ['rate' => $rate])
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save changes</x-dashboard.button>
                <x-dashboard.button :href="route('admin.exchange-rates')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
