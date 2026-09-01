@extends('layouts.dashboard-admin')

@section('title', 'Withdrawal '.$withdrawal->reference)

@section('content')
@php
    $initiate = $withdrawal->providerInitiateSnapshot();
    $summary = (array) (($withdrawal->provider_meta ?? [])['last_summary'] ?? []);
    $fee = $initiate['totalFee'] ?? $initiate['fee'] ?? $summary['totalFee'] ?? $summary['fee'] ?? null;
    $canAuthorize = $withdrawal->needsProviderAuthorization()
        && ! $withdrawal->isProviderAuthorizationExpired()
        && (int) $withdrawal->provider_auth_attempts < $maxAuthAttempts;
@endphp
<x-layout.page
    :title="'Withdrawal '.$withdrawal->reference"
    subtitle="Review payout details and provider authorization."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Withdrawals', route('admin.withdrawals')],
        [$withdrawal->reference, null],
    ]"
>
    @if ($errors->any())
        <x-dashboard.alert type="danger">{{ $errors->first() }}</x-dashboard.alert>
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
        </x-dashboard.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-dashboard.card class="space-y-3 text-sm">
            <h2 class="text-lg font-semibold text-text-primary">Withdrawal</h2>
            <dl class="space-y-2">
                <div class="flex justify-between gap-4"><dt class="text-text-muted">User</dt><dd>{{ \App\Models\User::labelFor($withdrawal->user) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Amount</dt><dd class="font-semibold">₦{{ number_format($withdrawal->amount, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Bank</dt><dd>{{ $withdrawal->bank_name }} · {{ $withdrawal->maskedAccountNumber() }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Account name</dt><dd>{{ $withdrawal->account_name }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Status</dt><dd><x-dashboard.badge :status="$withdrawal->status" /></dd></div>
                @if ($withdrawal->internal_status)
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Internal</dt><dd>{{ $withdrawal->internal_status }}</dd></div>
                @endif
            </dl>

            @if ($withdrawal->status === 'pending' || $withdrawal->internal_status === 'pending_review')
                <div class="flex flex-wrap gap-2 pt-2">
                    <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                        @csrf
                        <input type="hidden" name="confirm_send" value="1">
                        <x-dashboard.button type="submit" size="sm" variant="primary">Approve &amp; send</x-dashboard.button>
                    </form>
                    <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                        @csrf
                        <x-dashboard.button type="submit" size="sm" variant="danger">Reject</x-dashboard.button>
                    </form>
                </div>
            @elseif ($withdrawal->canBeRejectedByAdmin() && $withdrawal->needsProviderAuthorization())
                <div class="flex flex-wrap gap-2 pt-2">
                    <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" onsubmit="return confirm('Reject this withdrawal and return funds to the user? The Monnify transfer may still be pending on their side.');">
                        @csrf
                        <x-dashboard.button type="submit" size="sm" variant="danger">Reject &amp; unlock funds</x-dashboard.button>
                    </form>
                </div>
            @elseif ($withdrawal->status === 'failed' || $withdrawal->internal_status === 'failed')
                <form method="POST" action="{{ route('admin.withdrawals.retry', $withdrawal) }}" class="pt-2">
                    @csrf
                    <x-dashboard.button type="submit" size="sm" variant="secondary">Retry payout</x-dashboard.button>
                </form>
            @endif
        </x-dashboard.card>

        <x-dashboard.card class="space-y-3 text-sm">
            <h2 class="text-lg font-semibold text-text-primary">Payment provider</h2>
            <dl class="space-y-2">
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Provider</dt><dd>{{ $withdrawal->provider ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Payout reference</dt><dd class="font-mono text-xs break-all">{{ $withdrawal->provider_payout_reference ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Provider status</dt><dd>{{ $withdrawal->provider_status ?? '—' }}</dd></div>
                @if ($fee !== null)
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Fee</dt><dd>₦{{ number_format((float) $fee, 2) }}</dd></div>
                @endif
                @if (!empty($initiate['destinationBankName']))
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Destination bank</dt><dd>{{ $initiate['destinationBankName'] }}</dd></div>
                @endif
                @if (!empty($initiate['destinationAccountNumber']))
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Destination account</dt><dd>{{ $initiate['destinationAccountNumber'] }}</dd></div>
                @endif
                @if (!empty($initiate['dateCreated']))
                    <div class="flex justify-between gap-4"><dt class="text-text-muted">Initiated</dt><dd>{{ $initiate['dateCreated'] }}</dd></div>
                @endif
            </dl>
        </x-dashboard.card>
    </div>

    @if ($withdrawal->needsProviderAuthorization())
        <x-dashboard.card class="mt-6 space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-text-primary">Monnify authorization required</h2>
                <p class="mt-1 text-sm text-text-secondary">
                    Monnify sent an OTP to your registered merchant email. Enter it here to authorize this transfer.
                    OTP expiry is stated in that email; if authorization fails or status is EXPIRED, use Retry payout.
                </p>
            </div>

            @if ($withdrawal->isProviderAuthorizationExpired())
                <x-dashboard.alert type="warning">
                    This payout authorization has expired on Monnify. Do not submit an old OTP — use <strong>Retry payout</strong> to generate a new transfer reference.
                </x-dashboard.alert>
            @elseif ($canAuthorize)
                <form method="POST" action="{{ route('admin.withdrawals.authorize-provider', $withdrawal) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="min-w-[12rem] flex-1">
                        <x-dashboard.input name="authorization_code" label="Monnify authorization OTP" maxlength="12" required autocomplete="one-time-code" />
                    </div>
                    <x-dashboard.button type="submit" variant="primary">Authorize transfer</x-dashboard.button>
                </form>
                <p class="text-xs text-text-muted">
                    Attempts: {{ (int) $withdrawal->provider_auth_attempts }} / {{ $maxAuthAttempts }}
                </p>
            @else
                <x-dashboard.alert type="warning">
                    Authorization is not available (too many attempts or payout is no longer awaiting OTP).
                </x-dashboard.alert>
            @endif
        </x-dashboard.card>
    @endif

    <x-dashboard.card class="mt-6">
        <h2 class="mb-3 text-lg font-semibold text-text-primary">Timeline</h2>
        <ul class="space-y-2 text-sm">
            @forelse ($withdrawal->timelineEvents as $event)
                <li class="flex flex-wrap justify-between gap-2 border-b border-border-subtle pb-2">
                    <span>{{ $event->label }}</span>
                    <span class="text-text-muted">{{ $event->occurred_at?->toDayDateTimeString() }}</span>
                </li>
            @empty
                <li class="text-text-muted">No timeline events yet.</li>
            @endforelse
        </ul>
    </x-dashboard.card>
</x-layout.page>
@endsection
