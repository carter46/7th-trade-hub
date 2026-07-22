@extends('layouts.dashboard-admin')

@section('title', 'Add Exchange Rate')

@section('content')
<x-layout.page
    title="Add Exchange Rate"
    subtitle="Define buy and sell rates for an asset."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Exchange Rates', route('admin.exchange-rates')],
        ['Create', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.exchange-rates.store') }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @include('dashboard.admin.exchange-rates._form')
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Create rate</x-dashboard.button>
                <x-dashboard.button :href="route('admin.exchange-rates')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
