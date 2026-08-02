@extends('layouts.dashboard-admin')

@section('title', 'Edit Exchange Rate')

@section('content')
<x-layout.page
    title="Edit Exchange Rate"
    subtitle="Update {{ $rate->asset }} spread, networks, and limits. Buy rate = OTC market − this coin’s spread."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Coin Catalog', route('admin.exchange-rates')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.exchange-rates.update', $rate) }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            @include('dashboard.admin.exchange-rates._form')
            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save changes</x-dashboard.button>
                <x-dashboard.button :href="route('admin.exchange-rates')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
