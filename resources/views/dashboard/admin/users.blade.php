@extends('layouts.dashboard-admin')

@section('title', 'User Management')

@section('content')
@php
    $userRows = $users ?? collect();
@endphp
<x-layout.page title="User Management" subtitle="Manage platform users and roles." width="full">
    <x-dashboard.table
        :empty="$userRows->isEmpty()"
        empty-title="No users yet"
        empty-description="Registered accounts will appear here."
        empty-icon="users"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Name</x-dashboard.th>
            <x-dashboard.th>Email</x-dashboard.th>
            <x-dashboard.th>Role</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Joined</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>

        @foreach ($userRows as $u)
            <tr>
                <x-dashboard.td class="font-medium text-text-primary">{{ $u->name }}</x-dashboard.td>
                <x-dashboard.td class="text-text-secondary">{{ $u->email }}</x-dashboard.td>
                <x-dashboard.td>
                    @if ($u->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.role', $u) }}" class="flex gap-2 items-end" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <x-dashboard.select name="role" size="sm">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" @selected($u->hasRole($role->name))>{{ $role->name }}</option>
                                @endforeach
                            </x-dashboard.select>
                            <x-dashboard.button type="submit" variant="link" size="xs" x-bind:disabled="submitting">Set</x-dashboard.button>
                        </form>
                    @else
                        <span class="text-xs text-text-muted">{{ $u->roles->pluck('name')->join(', ') ?: '—' }}</span>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$u->is_suspended ? 'suspended' : 'active'" />
                </x-dashboard.td>
                <x-dashboard.td class="text-text-muted text-xs">{{ $u->created_at->format('M j, Y') }}</x-dashboard.td>
                <x-dashboard.td>
                    @if ($u->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.suspend', $u) }}" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <x-dashboard.button
                                type="submit"
                                size="xs"
                                :variant="$u->is_suspended ? 'success' : 'danger'"
                                x-bind:disabled="submitting"
                            >
                                {{ $u->is_suspended ? 'Unsuspend' : 'Suspend' }}
                            </x-dashboard.button>
                        </form>
                    @else
                        <span class="text-xs text-text-muted">You</span>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        @if (isset($users))
            <x-dashboard.pagination :paginator="$users" />
        @endif
    </x-slot:pagination>
</x-layout.page>
@endsection
