@extends('layouts.dashboard-admin')

@section('title', 'User Management')

@section('content')
@php
    $userRows = $users ?? collect();
@endphp
<x-layout.page title="User Management" subtitle="Manage platform users and roles." width="full">
    <x-ui.table
        :empty="$userRows->isEmpty()"
        empty-title="No users yet"
        empty-description="Registered accounts will appear here."
        empty-icon="users"
        striped
    >
        <x-slot:head>
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Email</x-ui.th>
            <x-ui.th>Role</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Joined</x-ui.th>
            <x-ui.th>Actions</x-ui.th>
        </x-slot:head>

        @foreach ($userRows as $u)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-medium">{{ $u->name }}</x-ui.td>
                <x-ui.td class="text-text-secondary">{{ $u->email }}</x-ui.td>
                <x-ui.td>
                    @if ($u->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.role', $u) }}" class="flex gap-2 items-end" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <x-ui.select name="role" size="sm">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" @selected($u->hasRole($role->name))>{{ $role->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.button type="submit" variant="link" size="xs" x-bind:disabled="submitting">Set</x-ui.button>
                        </form>
                    @else
                        <span class="text-xs text-text-muted">{{ $u->roles->pluck('name')->join(', ') ?: '—' }}</span>
                    @endif
                </x-ui.td>
                <x-ui.td>
                    <x-ui.badge :status="$u->is_suspended ? 'suspended' : 'active'" />
                </x-ui.td>
                <x-ui.td class="text-text-muted text-xs">{{ $u->created_at->format('M j, Y') }}</x-ui.td>
                <x-ui.td>
                    @if ($u->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.suspend', $u) }}" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <x-ui.button
                                type="submit"
                                size="xs"
                                :variant="$u->is_suspended ? 'success' : 'danger'"
                                x-bind:disabled="submitting"
                            >
                                {{ $u->is_suspended ? 'Unsuspend' : 'Suspend' }}
                            </x-ui.button>
                        </form>
                    @else
                        <span class="text-xs text-text-muted">You</span>
                    @endif
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        @if (isset($users))
            <x-ui.pagination :paginator="$users" />
        @endif
    </x-slot:pagination>
</x-layout.page>
@endsection
