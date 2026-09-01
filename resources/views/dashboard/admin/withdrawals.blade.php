@extends('layouts.dashboard-admin')

@section('title', 'Withdrawals')

@section('content')
@php
    $monnifyEnv = null;
    if ($monnifyEnabled ?? false) {
        $provider = \App\Models\IntegrationProvider::forProvider(\App\Models\IntegrationProvider::MONNIFY);
        $monnifyEnv = (string) ($provider->meta['environment'] ?? 'sandbox');
    }
@endphp
<x-layout.page
    title="Withdrawals"
    subtitle="Approve and send payouts to verified bank snapshots."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Withdrawals', null],
    ]"
>
    @if ($errors->any())
        <x-dashboard.alert type="danger">
            {{ $errors->first() }}
        </x-dashboard.alert>
    @endif
    @if (session('status'))
        <x-dashboard.alert type="success">{{ session('status') }}</x-dashboard.alert>
    @endif
    @if (session('error'))
        <x-dashboard.alert type="danger">{{ session('error') }}</x-dashboard.alert>
    @endif

    @if ($monnifyEnabled && $merchantBalance !== null)
        <x-dashboard.alert type="info" class="mb-4">
            Merchant disbursement balance: <strong>₦{{ number_format($merchantBalance, 2) }}</strong>
            @if ($monnifyEnv === 'sandbox')
                <span class="block mt-1 text-xs">Sandbox mode — payouts use Monnify test disbursements. Status should move to processing or completed after approve; errors appear above.</span>
            @endif
        </x-dashboard.alert>
    @endif

    <x-dashboard.table :empty="$withdrawals->isEmpty()" empty-title="No withdrawals" empty-description="Pending bank withdrawal requests will appear here." empty-icon="withdraw" striped :min-height="false">
        <x-slot:head>
            <x-dashboard.th>User</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th class="hidden sm:table-cell">Bank (snapshot)</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($withdrawals as $w)
            <tr>
                <x-dashboard.td class="min-w-0 max-w-[9rem] sm:max-w-none">
                    <div class="font-medium text-text-primary">{{ \App\Models\User::labelFor($w->user) }}</div>
                    <div class="mt-0.5 font-mono text-[10px] leading-snug text-text-muted break-all">{{ $w->reference }}</div>
                </x-dashboard.td>
                <x-dashboard.td class="whitespace-nowrap">₦{{ number_format($w->amount, 2) }}</x-dashboard.td>
                <x-dashboard.td class="hidden text-sm sm:table-cell">
                    {{ $w->bank_name }} · {{ $w->maskedAccountNumber() }}<br>
                    <span class="text-text-muted">{{ $w->account_name }}</span>
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$w->status" />
                    @if ($w->internal_status)
                        <div class="text-xs text-text-muted mt-1">{{ $w->internal_status }}</div>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    @if ($w->status === 'pending' || $w->internal_status === 'pending_review')
                        <x-dashboard.row-actions>
                            <x-dashboard.menu-item type="button" variant="success" @click="$dispatch('open-modal', 'approve-wd-{{ $w->id }}')">Approve and send</x-dashboard.menu-item>
                            <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'reject-wd-{{ $w->id }}')">Reject</x-dashboard.menu-item>
                        </x-dashboard.row-actions>
                    @elseif ($w->status === 'failed' || $w->internal_status === 'failed')
                        <form method="POST" action="{{ route('admin.withdrawals.retry', $w) }}">
                            @csrf
                            <x-dashboard.button type="submit" size="sm" variant="secondary">Retry payout</x-dashboard.button>
                        </form>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    @foreach ($withdrawals as $w)
        @if ($w->status === 'pending' || $w->internal_status === 'pending_review')
            <x-dashboard.modal name="approve-wd-{{ $w->id }}" title="Approve and send?" confirm-label="Approve and send" :form-action="route('admin.withdrawals.approve', $w)">
                <div class="space-y-3 text-sm">
                    <p>This initiates a bank transfer via Monnify.</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>User: {{ \App\Models\User::labelFor($w->user) }}</li>
                        <li>Amount: ₦{{ number_format($w->amount, 2) }}</li>
                        <li>Recipient: {{ $w->account_name }}</li>
                        <li>Bank: {{ $w->bank_name }} {{ $w->maskedAccountNumber() }}</li>
                        <li>Reference: {{ $w->reference }}</li>
                        @if ($merchantBalance !== null)
                            <li>Merchant balance: ₦{{ number_format($merchantBalance, 2) }}</li>
                            @if ($merchantBalance < (float) $w->amount)
                                <li class="text-danger font-medium">Insufficient merchant balance — do not confirm.</li>
                            @endif
                        @endif
                    </ul>
                    <input type="hidden" name="confirm_send" value="1">
                    <label class="block text-sm">
                        <span class="text-text-secondary">Approval note (optional)</span>
                        <input type="text" name="approval_note" class="mt-1 w-full rounded-lg border border-border-subtle px-3 py-2" maxlength="500">
                    </label>
                </div>
            </x-dashboard.modal>
            <x-dashboard.modal name="reject-wd-{{ $w->id }}" title="Reject withdrawal?" variant="danger" confirm-label="Reject" :form-action="route('admin.withdrawals.reject', $w)">
                Locked funds will be returned to the user wallet.
            </x-dashboard.modal>
        @endif
    @endforeach

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$withdrawals" />
    </x-slot:pagination>
</x-layout.page>
@endsection
