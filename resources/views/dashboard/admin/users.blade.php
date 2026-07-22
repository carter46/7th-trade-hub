@extends('layouts.dashboard-admin')

@section('title', 'User Management')

@section('content')
@php
    $userRows = $users ?? collect();
    $status = $status ?? 'active';
@endphp
<x-layout.page
    title="User Management"
    subtitle="Member accounts only. Administrators are managed separately."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Users', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('admin.users.create')" size="sm">Create user</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.tabs
        :active="$status"
        :tabs="[
            ['id' => 'active', 'label' => 'Active', 'href' => route('admin.users', ['status' => 'active']), 'count' => $activeCount ?? null],
            ['id' => 'suspended', 'label' => 'Suspended', 'href' => route('admin.users', ['status' => 'suspended']), 'count' => $suspendedCount ?? null],
        ]"
        class="mb-4"
    />

    <x-dashboard.table
        :empty="$userRows->isEmpty()"
        :empty-title="$status === 'suspended' ? 'No suspended users' : 'No active users'"
        empty-description="Member accounts will appear here."
        empty-icon="users"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Name</x-dashboard.th>
            <x-dashboard.th>Email</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>

        @foreach ($userRows as $u)
            <tr>
                <x-dashboard.td>
                    <a href="{{ route('admin.users.show', $u) }}" class="font-medium text-text-primary hover:text-primary focus-ring rounded">
                        {{ $u->name }}
                    </a>
                    <div class="text-xs text-text-muted mt-0.5">Joined {{ $u->created_at->format('j M Y') }}</div>
                </x-dashboard.td>
                <x-dashboard.td class="text-text-secondary">{{ $u->email }}</x-dashboard.td>
                <x-dashboard.td>
                    @if ($u->anonymized_at)
                        <x-dashboard.badge status="neutral">Deleted</x-dashboard.badge>
                    @else
                        <x-dashboard.badge :status="$u->is_suspended ? 'suspended' : 'active'" />
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    @if ($u->anonymized_at)
                        <span class="text-xs text-text-muted">—</span>
                    @else
                        <x-dashboard.row-actions>
                            <x-dashboard.menu-item :href="route('admin.users.show', $u)">View</x-dashboard.menu-item>
                            <x-dashboard.menu-item :href="route('admin.users.edit', $u)">Edit</x-dashboard.menu-item>
                            @if (! $u->is_suspended)
                                <form method="POST" action="{{ route('admin.users.impersonate', $u) }}">
                                    @csrf
                                    <x-dashboard.menu-item type="submit">Login as user</x-dashboard.menu-item>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.users.password-reset', $u) }}">
                                @csrf
                                <x-dashboard.menu-item type="submit">Send password reset</x-dashboard.menu-item>
                            </form>

                            @if ($u->is_suspended)
                                <form method="POST" action="{{ route('admin.users.restore', $u) }}">
                                    @csrf
                                    <x-dashboard.menu-item type="submit" variant="success">Restore</x-dashboard.menu-item>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Permanently delete this user? This anonymizes personal data and cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <x-dashboard.menu-item type="submit" variant="danger">Permanently Delete</x-dashboard.menu-item>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.suspend', $u) }}">
                                    @csrf
                                    <x-dashboard.menu-item type="submit" variant="danger">Suspend</x-dashboard.menu-item>
                                </form>
                            @endif
                        </x-dashboard.row-actions>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$users" />
    </x-slot:pagination>
</x-layout.page>
@endsection
