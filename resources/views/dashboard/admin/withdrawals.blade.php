@extends('layouts.dashboard-admin')

@section('title', 'Withdrawals')

@section('content')
<x-layout.page title="Withdrawals" subtitle="Approve or reject user withdrawal requests" width="full">
    <x-ui.table :empty="$withdrawals->isEmpty()" empty-title="No withdrawals" empty-description="Pending bank withdrawal requests will appear here." empty-icon="withdraw" striped>
        <x-slot:head>
            <x-ui.th>Reference</x-ui.th>
            <x-ui.th>User</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Actions</x-ui.th>
        </x-slot:head>
        @foreach ($withdrawals as $w)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-medium">{{ $w->reference }}</x-ui.td>
                <x-ui.td>{{ $w->user->email }}</x-ui.td>
                <x-ui.td>₦{{ number_format($w->amount, 2) }}</x-ui.td>
                <x-ui.td><x-ui.badge :status="$w->status" /></x-ui.td>
                <x-ui.td>
                    @if ($w->status === 'pending')
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button type="button" size="xs" variant="success" @click="$dispatch('open-modal', 'approve-wd-{{ $w->id }}')">Approve</x-ui.button>
                            <x-ui.button type="button" size="xs" variant="danger" @click="$dispatch('open-modal', 'reject-wd-{{ $w->id }}')">Reject</x-ui.button>
                            <x-ui.modal name="approve-wd-{{ $w->id }}" title="Approve withdrawal?" confirm-label="Approve" :form-action="route('admin.withdrawals.approve', $w)">
                                Pay out ₦{{ number_format($w->amount, 2) }} for {{ $w->user->email }}.
                            </x-ui.modal>
                            <x-ui.modal name="reject-wd-{{ $w->id }}" title="Reject withdrawal?" variant="danger" confirm-label="Reject" :form-action="route('admin.withdrawals.reject', $w)">
                                Locked funds will be returned to the user wallet.
                            </x-ui.modal>
                        </div>
                    @endif
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>
    <x-slot:pagination>
        <x-ui.pagination :paginator="$withdrawals" />
    </x-slot:pagination>
</x-layout.page>
@endsection
