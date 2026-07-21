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
    @if (session('status'))
        <x-dashboard.alert variant="success">{{ session('status') }}</x-dashboard.alert>
    @endif
    @if (session('error'))
        <x-dashboard.alert variant="error">{{ session('error') }}</x-dashboard.alert>
    @endif

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
                    <x-dashboard.row-actions>
                        @if (! $u->anonymized_at)
                            <a href="{{ route('admin.users.edit', $u) }}" role="menuitem" class="block rounded-lg px-3 py-2 text-sm text-text-primary hover:bg-muted/60">Edit</a>
                        @endif

                        @if ($u->is_suspended)
                            @if (! $u->anonymized_at)
                                <form method="POST" action="{{ route('admin.users.restore', $u) }}">
                                    @csrf
                                    <button type="submit" role="menuitem" class="w-full rounded-lg px-3 py-2 text-left text-sm text-success hover:bg-muted/60">Restore</button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Permanently delete this user? This anonymizes personal data and cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" role="menuitem" class="w-full rounded-lg px-3 py-2 text-left text-sm text-danger hover:bg-muted/60">Permanently Delete</button>
                                </form>
                            @endif
                        @else
                            <form method="POST" action="{{ route('admin.users.suspend', $u) }}">
                                @csrf
                                <button type="submit" role="menuitem" class="w-full rounded-lg px-3 py-2 text-left text-sm text-danger hover:bg-muted/60">Suspend</button>
                            </form>
                        @endif
                    </x-dashboard.row-actions>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$users" />
    </x-slot:pagination>
</x-layout.page>
@endsection
