@php
    $userRows = $users ?? collect();
    $status = $status ?? 'active';
@endphp

<x-dashboard.table
    :empty="$userRows->isEmpty()"
    :empty-title="$status === 'suspended' ? 'No suspended users' : 'No active users'"
    empty-description="Member accounts will appear here."
    empty-icon="users"
    striped
>
    <x-slot:head>
        <x-dashboard.th>User</x-dashboard.th>
        <x-dashboard.th>Email</x-dashboard.th>
        <x-dashboard.th>Status</x-dashboard.th>
        <x-dashboard.th>Actions</x-dashboard.th>
    </x-slot:head>

    @foreach ($userRows as $u)
        @php $avatarUrl = $u->avatarUrl(); @endphp
        <tr>
            <x-dashboard.td>
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-border-default bg-primary/15 text-xs font-semibold text-primary">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover">
                        @else
                            <span aria-hidden="true">{{ $u->initials() }}</span>
                        @endif
                    </span>
                    <div class="min-w-0">
                        <a href="{{ route('admin.users.show', $u) }}" class="font-medium text-text-primary hover:text-primary focus-ring rounded">
                            {{ $u->name }}
                        </a>
                        <div class="text-xs text-text-muted mt-0.5">Joined {{ $u->created_at->format('j M Y') }}</div>
                    </div>
                </div>
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
                            <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'delete-user-{{ $u->id }}')">
                                Permanently Delete
                            </x-dashboard.menu-item>
                            <x-dashboard.modal
                                name="delete-user-{{ $u->id }}"
                                title="Permanently delete this user?"
                                variant="danger"
                                confirm-label="Permanently Delete"
                                :form-action="route('admin.users.destroy', $u)"
                                method="DELETE"
                            >
                                This anonymizes personal data and cannot be undone.
                            </x-dashboard.modal>
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

<div class="mt-4">
    <x-dashboard.pagination :paginator="$users" />
</div>
