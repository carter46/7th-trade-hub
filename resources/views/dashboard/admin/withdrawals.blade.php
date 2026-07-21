@extends('layouts.dashboard-admin')

@section('title', 'Withdrawals')

@section('content')
<x-layout.page title="Withdrawals" subtitle="Approve or reject user withdrawal requests" width="full">
    <x-dashboard.table :empty="$withdrawals->isEmpty()" empty-title="No withdrawals" empty-description="Pending bank withdrawal requests will appear here." empty-icon="withdraw" striped>
        <x-slot:head>
            <x-dashboard.th>Reference</x-dashboard.th>
            <x-dashboard.th>User</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($withdrawals as $w)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-medium">{{ $w->reference }}</x-dashboard.td>
                <x-dashboard.td>{{ $w->user->email }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($w->amount, 2) }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$w->status" /></x-dashboard.td>
                <x-dashboard.td>
                    @if ($w->status === 'pending')
                        <div class="flex flex-wrap gap-2">
                            <x-dashboard.button type="button" size="xs" variant="success" @click="$dispatch('open-modal', 'approve-wd-{{ $w->id }}')">Approve</x-dashboard.button>
                            <x-dashboard.button type="button" size="xs" variant="danger" @click="$dispatch('open-modal', 'reject-wd-{{ $w->id }}')">Reject</x-dashboard.button>
                            <x-dashboard.modal name="approve-wd-{{ $w->id }}" title="Approve withdrawal?" confirm-label="Approve" :form-action="route('admin.withdrawals.approve', $w)">
                                Pay out ₦{{ number_format($w->amount, 2) }} for {{ $w->user->email }}.
                            </x-dashboard.modal>
                            <x-dashboard.modal name="reject-wd-{{ $w->id }}" title="Reject withdrawal?" variant="danger" confirm-label="Reject" :form-action="route('admin.withdrawals.reject', $w)">
                                Locked funds will be returned to the user wallet.
                            </x-dashboard.modal>
                        </div>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$withdrawals" />
    </x-slot:pagination>
</x-layout.page>
@endsection
