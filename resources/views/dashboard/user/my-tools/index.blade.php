@extends('layouts.dashboard-user')

@section('title', 'My Tools')

@section('content')
<x-layout.page
    title="My Tools"
    subtitle="Websites and domains you own — separate from order history."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['My Tools', null],
    ]"
>
    @include('dashboard.user.my-tools._tabs', ['activeTab' => $activeTab])

    <div class="mt-6">
        @if ($activeTab === 'domains')
            <p class="mb-4 max-w-3xl text-sm leading-relaxed text-text-secondary">
                Each domain has its own nameserver configuration. Use Manage to point a domain at your hosting or DNS provider.
            </p>
            @include('dashboard.user.my-tools._domains-table')
        @else
            <p class="mb-4 max-w-3xl text-sm leading-relaxed text-text-secondary">
                Website packages and templates you have purchased appear here for setup, access, and renewal. Order receipts stay under Service orders.
            </p>
            @include('dashboard.user.my-tools._websites-table')
        @endif
    </div>
</x-layout.page>
@endsection
