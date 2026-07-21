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

    @if (session('status'))
        <x-dashboard.alert variant="success">{{ session('status') }}</x-dashboard.alert>
    @endif
    @if (session('error'))
        <x-dashboard.alert variant="error">{{ session('error') }}</x-dashboard.alert>
    @endif

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
                        <a href="{{ route('admin.administrators.edit', $admin) }}" role="menuitem" class="block rounded-lg px-3 py-2 text-sm text-text-primary hover:bg-muted/60">Edit</a>
                        @if ($admin->id !== auth()->id())
                            @if ($admin->is_suspended)
                                <form method="POST" action="{{ route('admin.administrators.restore', $admin) }}">
                                    @csrf
                                    <button type="submit" role="menuitem" class="w-full rounded-lg px-3 py-2 text-left text-sm text-success hover:bg-muted/60">Restore</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.administrators.suspend', $admin) }}">
                                    @csrf
                                    <button type="submit" role="menuitem" class="w-full rounded-lg px-3 py-2 text-left text-sm text-danger hover:bg-muted/60">Suspend</button>
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
