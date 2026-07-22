@extends('layouts.dashboard-admin')

@section('title', 'KYC Review')

@section('content')
<x-layout.page
    title="KYC submissions"
    subtitle="Approve or reject identity verification requests."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['KYC submissions', null],
    ]"
>
    <x-dashboard.table :empty="$submissions->isEmpty()" empty-title="No KYC submissions" empty-description="Pending identity checks will appear here." empty-icon="kyc" striped>
        <x-slot:head>
            <x-dashboard.th>User</x-dashboard.th>
            <x-dashboard.th>Level</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($submissions as $s)
            <tr>
                <x-dashboard.td>{{ $s->user->email }}</x-dashboard.td>
                <x-dashboard.td>Level {{ $s->level_requested }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$s->status" /></x-dashboard.td>
                <x-dashboard.td>
                    @if ($s->status === 'pending')
                        <x-dashboard.row-actions>
                            <x-dashboard.menu-item type="button" variant="success" @click="$dispatch('open-modal', 'approve-kyc-{{ $s->id }}')">Approve</x-dashboard.menu-item>
                            <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'reject-kyc-{{ $s->id }}')">Reject</x-dashboard.menu-item>
                        </x-dashboard.row-actions>
                        <x-dashboard.modal name="approve-kyc-{{ $s->id }}" title="Approve KYC?" confirm-label="Approve" :form-action="route('admin.kyc.approve', $s)">
                            Grant level {{ $s->level_requested }} to {{ $s->user->email }}.
                        </x-dashboard.modal>
                        <x-dashboard.modal name="reject-kyc-{{ $s->id }}" title="Reject KYC?" variant="danger" confirm-label="Reject" :form-action="route('admin.kyc.reject', $s)">
                            The user will need to resubmit documentation.
                        </x-dashboard.modal>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$submissions" />
    </x-slot:pagination>
</x-layout.page>
@endsection
