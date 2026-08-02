@extends('layouts.dashboard-admin')

@section('title', 'Add Deposit Wallet')

@section('content')
<x-layout.page title="Add deposit wallet" width="full" :breadcrumb="[['Admin', route('admin')], ['Wallets', route('admin.crypto-wallets')], ['Create', null]]">
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.crypto-wallets.store') }}" class="space-y-4">
            @csrf
            @include('dashboard.admin.crypto-wallets._form', [
                'networksByCoin' => $networksByCoin,
                'catalogCoins' => $catalogCoins,
                'maxActive' => $maxActive,
            ])
            <x-dashboard.button type="submit" variant="primary">Save wallet</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
