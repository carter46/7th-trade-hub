@extends('layouts.dashboard-user')

@section('title', 'My Tools')

@section('content')
@php
    $tabs = [
        ['id' => 'websites', 'label' => 'My Websites', 'href' => route('dashboard.my-tools')],
        ['id' => 'domains', 'label' => 'My Domains', 'href' => route('dashboard.my-tools.domains')],
    ];
@endphp
<x-layout.page
    title="My Tools"
    subtitle="Websites and domains you own — separate from order history."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['My Tools', null],
    ]"
>
    <x-dashboard.ajax-tabs variant="pills" :tabs="$tabs" :active="$activeTab" />

    <div id="dashboard-tab-panel" class="mt-6">
        @include('dashboard.user.my-tools._panel-'.$activeTab)
    </div>
</x-layout.page>
@endsection
