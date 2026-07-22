@extends('layouts.dashboard-admin')

@section('title', 'Administrators')

@section('content')
@php
    $rows = $administrators ?? collect();
@endphp
<x-layout.page
    title="Administrators"
    subtitle="Manage staff accounts with the admin role."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Administrators', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button href="{{ route('admin.administrators.create') }}" variant="primary" size="sm" icon="plus">
            New Administrator
        </x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$rows->isEmpty()"
        empty-title="No administrators"
        empty-description="Create a staff account to get started."
        empty-icon="verified"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Name</x-dashboard.th>
            <x-dashboard.th>Email</x-dashboard.th>
            <x-dashboard.th>Admins manage</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>

        @foreach ($rows as $admin)
            <tr>
                <x-dashboard.td>
                    <div class="font-medium text-text-primary">{{ $admin->name }}</div>
                    <div class="text-xs text-text-muted">{{ '@'.$admin->username }}</div>
                </x-dashboard.td>
                <x-dashboard.td class="text-text-secondary">{{ $admin->email }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$admin->can('admins.manage') ? 'active' : 'neutral'">
                        {{ $admin->can('admins.manage') ? 'Yes' : 'No' }}
                    </x-dashboard.badge>
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$admin->is_suspended ? 'suspended' : 'active'" />
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.row-actions>
                        <x-dashboard.menu-item :href="route('admin.administrators.edit', $admin)">Edit</x-dashboard.menu-item>
                        @if ($admin->id !== auth()->id())
                            @if ($admin->is_suspended)
                                <form method="POST" action="{{ route('admin.administrators.restore', $admin) }}">
                                    @csrf
                                    <x-dashboard.menu-item type="submit" variant="success">Restore</x-dashboard.menu-item>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.administrators.suspend', $admin) }}">
                                    @csrf
                                    <x-dashboard.menu-item type="submit" variant="danger">Suspend</x-dashboard.menu-item>
                                </form>
                            @endif
                        @endif
                    </x-dashboard.row-actions>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$administrators" />
    </x-slot:pagination>
</x-layout.page>
@endsection
