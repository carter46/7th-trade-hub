@extends('layouts.dashboard-admin')

@section('title', 'Edit Administrator')

@section('content')
<x-layout.page
    title="Edit Administrator"
    subtitle="Update staff profile and administrator privileges."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Administrators', route('admin.administrators')],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.administrators.update', $administrator) }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-dashboard.input label="Full name" name="name" :value="old('name', $administrator->name)" required />
            <x-dashboard.input label="Username" name="username" :value="old('username', $administrator->username)" required />
            <x-dashboard.input label="Email" name="email" type="email" :value="old('email', $administrator->email)" required />
            <x-dashboard.input label="New password" name="password" type="password" hint="Leave blank to keep the current password." />
            <x-dashboard.input label="Confirm new password" name="password_confirmation" type="password" />

            <label class="flex items-start gap-3 text-sm text-text-secondary">
                <input
                    type="checkbox"
                    name="grant_admins_manage"
                    value="1"
                    class="mt-1 rounded border-border-default"
                    @checked(old('grant_admins_manage', $administrator->can('admins.manage')))
                >
                <span>
                    <span class="block font-medium text-text-primary">Grant admins.manage</span>
                    Allow this administrator to create and manage other administrators.
                </span>
            </label>

            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save changes</x-dashboard.button>
                <x-dashboard.button href="{{ route('admin.administrators') }}" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
