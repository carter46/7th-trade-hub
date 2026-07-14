@extends('layouts.dashboard-user')

@section('title', 'Profile')

@section('content')
<x-layout.page title="Profile" subtitle="Manage your account details and security." width="form">
    <div class="space-y-6">
        <x-ui.card>
            @include('profile.partials.update-profile-information-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.update-password-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.delete-user-form')
        </x-ui.card>
    </div>
</x-layout.page>
@endsection
