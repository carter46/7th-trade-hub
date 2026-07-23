@extends('layouts.dashboard-admin')

@section('title', 'User Management')

@section('content')
@php
    $status = $status ?? 'active';
@endphp
<x-layout.page
    title="User Management"
    subtitle="Member accounts only. Administrators are managed separately."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Users', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.users.create')" size="sm">Create user</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.card class="mb-4">
        <form method="GET" action="{{ route('admin.users') }}" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="min-w-[16rem] flex-1">
                <x-dashboard.input name="q" label="Search" :value="$search ?? ''" placeholder="Name, email, username..." />
            </div>
            <x-dashboard.button type="submit" variant="secondary">Search</x-dashboard.button>
        </form>
    </x-dashboard.card>

    <x-dashboard.ajax-tabs
        :active="$status"
        :tabs="[
            ['id' => 'active', 'label' => 'Active', 'href' => route('admin.users', ['status' => 'active']), 'count' => $activeCount ?? null],
            ['id' => 'suspended', 'label' => 'Suspended', 'href' => route('admin.users', ['status' => 'suspended']), 'count' => $suspendedCount ?? null],
        ]"
        class="mb-4"
    />

    <div id="dashboard-tab-panel">
        @include('dashboard.admin.users._table')
    </div>
</x-layout.page>
@endsection
