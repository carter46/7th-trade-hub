@extends('layouts.dashboard-admin')

@section('title', 'Edit User')

@section('content')
<x-layout.page
    title="Edit User"
    subtitle="{{ $user->email }}"
    width="form"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Users', route('admin.users')],
        [$user->name, route('admin.users.show', $user)],
        ['Edit', null],
    ]"
>
    <x-dashboard.card>
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-dashboard.input name="name" label="Name" :value="old('name', $user->name)" required />
            <x-dashboard.input name="username" label="Username" :value="old('username', $user->username)" required />
            <x-dashboard.input name="email" type="email" label="Email" :value="old('email', $user->email)" required />
            <x-dashboard.input name="phone" label="Phone" :value="old('phone', $user->phone)" />
            <x-dashboard.input name="country" label="Country (ISO-2)" :value="old('country', $user->country)" maxlength="2" />
            <x-dashboard.textarea name="bio" label="Bio">{{ old('bio', $user->bio) }}</x-dashboard.textarea>

            <div class="flex gap-3">
                <x-dashboard.button type="submit" x-bind:disabled="submitting">Save</x-dashboard.button>
                <x-dashboard.button :href="route('admin.users.show', $user)" variant="secondary">Cancel</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
