@extends('layouts.dashboard-admin')

@section('title', 'New Administrator')

@section('content')
<x-layout.page
    title="New Administrator"
    subtitle="Create a staff account with the admin role."
    width="form"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Administrators', route('admin.administrators')],
        ['Create', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.administrators.store') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <x-dashboard.input label="Full name" name="name" :value="old('name')" required />
            <x-dashboard.input label="Username" name="username" :value="old('username')" required />
            <x-dashboard.input label="Email" name="email" type="email" :value="old('email')" required />
            <x-dashboard.input label="Password" name="password" type="password" required />
            <x-dashboard.input label="Confirm password" name="password_confirmation" type="password" required />

            <label class="flex items-start gap-3 text-sm text-text-secondary">
                <input
                    type="checkbox"
                    name="grant_admins_manage"
                    value="1"
                    class="mt-1 rounded border-border-default"
                    @checked(old('grant_admins_manage'))
                >
                <span>
                    <span class="block font-medium text-text-primary">Grant admins.manage</span>
                    Allow this administrator to create and manage other administrators.
                </span>
            </label>

            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Create administrator</x-dashboard.button>
                <x-dashboard.button href="{{ route('admin.administrators') }}" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
