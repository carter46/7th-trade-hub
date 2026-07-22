@extends('layouts.dashboard-admin')

@section('title', 'Create User')

@section('content')
<x-layout.page
    title="Create User"
    subtitle="Create a member account with profile, verification, and optional wallet setup."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Users', route('admin.users')],
        ['Create', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.users.store') }}" class="w-full space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <x-dashboard.input label="Full name" name="name" :value="old('name')" required />
            <x-dashboard.input label="Username" name="username" :value="old('username')" required />
            <x-dashboard.input label="Email" name="email" type="email" :value="old('email')" required />
            <x-dashboard.input label="Password" name="password" type="password" required />
            <x-dashboard.input label="Confirm password" name="password_confirmation" type="password" required />
            <x-dashboard.input label="Phone" name="phone" :value="old('phone')" />
            <x-dashboard.input label="Country (ISO-2)" name="country" :value="old('country')" maxlength="2" />
            <x-dashboard.textarea name="bio" label="Bio">{{ old('bio') }}</x-dashboard.textarea>

            <x-dashboard.select label="KYC level" name="kyc_level">
                @foreach ([0 => '0 — None', 1 => '1 — Basic', 2 => '2 — Identity', 3 => '3 — Address', 4 => '4 — Enhanced'] as $value => $label)
                    <option value="{{ $value }}" @selected((string) old('kyc_level', '0') === (string) $value)>{{ $label }}</option>
                @endforeach
            </x-dashboard.select>

            <label class="flex items-start gap-3 text-sm text-text-secondary">
                <input type="checkbox" name="email_verified" value="1" class="mt-1 rounded border-border-default" @checked(old('email_verified', true))>
                <span>
                    <span class="block font-medium text-text-primary">Mark email as verified</span>
                    Skip OTP verification for this account.
                </span>
            </label>

            <label class="flex items-start gap-3 text-sm text-text-secondary">
                <input type="checkbox" name="is_suspended" value="1" class="mt-1 rounded border-border-default" @checked(old('is_suspended'))>
                <span>
                    <span class="block font-medium text-text-primary">Create as suspended</span>
                    Account cannot sign in until restored.
                </span>
            </label>

            <label class="flex items-start gap-3 text-sm text-text-secondary">
                <input type="checkbox" name="provision_wallet" value="1" class="mt-1 rounded border-border-default" @checked(old('provision_wallet'))>
                <span>
                    <span class="block font-medium text-text-primary">Provision wallet</span>
                    Requires KYC level 1 or higher.
                </span>
            </label>

            <div class="flex flex-wrap gap-2 pt-2">
                <x-dashboard.button type="submit" x-bind:disabled="submitting">Create user</x-dashboard.button>
                <x-dashboard.button :href="route('admin.users')" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
