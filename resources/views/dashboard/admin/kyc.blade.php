@extends('layouts.dashboard-admin')

@section('title', 'KYC Review')

@section('content')
<x-layout.page title="KYC submissions" subtitle="Approve or reject identity verification requests" width="full">
    <x-ui.table :empty="$submissions->isEmpty()" empty-title="No KYC submissions" empty-description="Pending identity checks will appear here." empty-icon="kyc" striped>
        <x-slot:head>
            <x-ui.th>User</x-ui.th>
            <x-ui.th>Level</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Actions</x-ui.th>
        </x-slot:head>
        @foreach ($submissions as $s)
            <tr class="hover:bg-muted/50">
                <x-ui.td>{{ $s->user->email }}</x-ui.td>
                <x-ui.td>Level {{ $s->level_requested }}</x-ui.td>
                <x-ui.td><x-ui.badge :status="$s->status" /></x-ui.td>
                <x-ui.td>
                    @if ($s->status === 'pending')
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button type="button" size="xs" variant="success" @click="$dispatch('open-modal', 'approve-kyc-{{ $s->id }}')">Approve</x-ui.button>
                            <x-ui.button type="button" size="xs" variant="danger" @click="$dispatch('open-modal', 'reject-kyc-{{ $s->id }}')">Reject</x-ui.button>
                            <x-ui.modal name="approve-kyc-{{ $s->id }}" title="Approve KYC?" confirm-label="Approve" :form-action="route('admin.kyc.approve', $s)">
                                Grant level {{ $s->level_requested }} to {{ $s->user->email }}.
                            </x-ui.modal>
                            <x-ui.modal name="reject-kyc-{{ $s->id }}" title="Reject KYC?" variant="danger" confirm-label="Reject" :form-action="route('admin.kyc.reject', $s)">
                                The user will need to resubmit documentation.
                            </x-ui.modal>
                        </div>
                    @endif
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>
    <x-slot:pagination>
        <x-ui.pagination :paginator="$submissions" />
    </x-slot:pagination>
</x-layout.page>
@endsection
