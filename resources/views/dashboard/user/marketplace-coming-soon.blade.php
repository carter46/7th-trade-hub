@extends('layouts.dashboard-user')

@section('title', 'Marketplace — Coming Soon')

@section('content')
<x-layout.page
    title="Marketplace"
    width="default"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Marketplace', null],
    ]"
>
    <x-dashboard.card class="max-w-xl mx-auto text-center py-12">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-primary mb-4">Coming soon</p>
        <h2 class="text-2xl font-bold text-text-primary">Marketplace is on the way</h2>
        <p class="mt-3 text-sm text-text-secondary leading-relaxed">
            We are building a place to browse, buy, and sell digital products with escrow protection.
            Platform services and your wallet are still available.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <x-dashboard.button :href="route('dashboard.services')" variant="primary">Browse services</x-dashboard.button>
            <x-dashboard.button :href="route('dashboard')" variant="secondary">Back to dashboard</x-dashboard.button>
        </div>
    </x-dashboard.card>
</x-layout.page>
@endsection
