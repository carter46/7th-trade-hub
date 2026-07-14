@extends('layouts.dashboard-admin')

@section('title', 'Deposits')

@section('content')
<x-layout.page title="Wallet fundings" subtitle="Review and approve bank deposit proofs" width="full">
    <x-ui.table :empty="$fundings->isEmpty()" empty-title="No deposit requests" empty-description="New bank transfer proofs will show up here for review." empty-icon="deposit" striped>
        <x-slot:head>
            <x-ui.th>Ref</x-ui.th>
            <x-ui.th>User</x-ui.th>
            <x-ui.th>Method</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Actions</x-ui.th>
        </x-slot:head>

        @foreach ($fundings as $f)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-medium">{{ $f->reference }}</x-ui.td>
                <x-ui.td>{{ $f->user->email }}</x-ui.td>
                <x-ui.td>{{ $f->method }}</x-ui.td>
                <x-ui.td>₦{{ number_format($f->amount, 2) }}</x-ui.td>
                <x-ui.td><x-ui.badge :status="$f->status" /></x-ui.td>
                <x-ui.td>
                    <div class="flex flex-wrap gap-2">
                        @if ($f->status === 'pending')
                            <x-ui.button type="button" size="xs" variant="success" @click="$dispatch('open-modal', 'approve-funding-{{ $f->id }}')">Approve</x-ui.button>
                            <x-ui.button type="button" size="xs" variant="danger" @click="$dispatch('open-modal', 'reject-funding-{{ $f->id }}')">Reject</x-ui.button>
                            <x-ui.modal
                                name="approve-funding-{{ $f->id }}"
                                title="Approve deposit?"
                                confirm-label="Approve"
                                :form-action="route('admin.fundings.approve', $f)"
                            >
                                Credit ₦{{ number_format($f->amount, 2) }} to {{ $f->user->email }}?
                            </x-ui.modal>
                            <x-ui.modal
                                name="reject-funding-{{ $f->id }}"
                                title="Reject deposit?"
                                variant="danger"
                                confirm-label="Reject"
                                :form-action="route('admin.fundings.reject', $f)"
                            >
                                This will mark the funding as rejected. No wallet credit will be issued.
                            </x-ui.modal>
                        @elseif ($f->status === 'approved')
                            <x-ui.button type="button" size="xs" variant="warning" @click="$dispatch('open-modal', 'reverse-funding-{{ $f->id }}')">Reverse</x-ui.button>
                            <x-ui.modal
                                name="reverse-funding-{{ $f->id }}"
                                title="Reverse deposit?"
                                variant="warning"
                                confirm-label="Reverse"
                                :form-action="route('admin.fundings.reverse', $f)"
                            >
                                <x-slot:form>
                                    <input type="hidden" name="reason" value="Admin reversal">
                                </x-slot:form>
                                This will debit the user wallet for ₦{{ number_format($f->amount, 2) }}.
                            </x-ui.modal>
                        @endif
                    </div>
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$fundings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
