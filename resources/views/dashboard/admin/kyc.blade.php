@extends('layouts.dashboard-admin')

@section('title', 'KYC Review')

@section('content')
@php
    $status = $status ?? 'pending';
    $search = $search ?? '';
    $filterQuery = array_filter(['q' => $search ?: null], fn ($v) => filled($v));
@endphp
<x-layout.page
    title="KYC submissions"
    subtitle="Approve, reject, or override identity verification."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['KYC submissions', null],
    ]"
>
    <x-dashboard.card class="mb-4">
        <form method="GET" action="{{ route('admin.kyc') }}" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="min-w-[16rem] flex-1">
                <x-dashboard.input name="q" label="Search user" :value="$search" placeholder="Name, email, username..." />
            </div>
            <x-dashboard.button type="submit" variant="secondary">Search</x-dashboard.button>
        </form>
    </x-dashboard.card>

    <x-dashboard.ajax-tabs
        :active="$status"
        :tabs="[
            ['id' => 'pending', 'label' => 'Pending', 'href' => route('admin.kyc', array_merge($filterQuery, ['status' => 'pending'])), 'count' => $counts['pending'] ?? 0],
            ['id' => 'approved', 'label' => 'Approved', 'href' => route('admin.kyc', array_merge($filterQuery, ['status' => 'approved'])), 'count' => $counts['approved'] ?? 0],
            ['id' => 'rejected', 'label' => 'Rejected', 'href' => route('admin.kyc', array_merge($filterQuery, ['status' => 'rejected'])), 'count' => $counts['rejected'] ?? 0],
        ]"
        class="mb-4"
    />

    <div id="dashboard-tab-panel">
        @include('dashboard.admin.kyc._panel')
    </div>
</x-layout.page>
@endsection
