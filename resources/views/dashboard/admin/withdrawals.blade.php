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
                    <div class="mt-0.5 font-mono text-[10px] leading-snug text-text-muted break-all">
                        <a href="{{ route('admin.withdrawals.show', $w) }}" class="underline">{{ $w->reference }}</a>
                    </div>
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
                    @if ($w->needsProviderAuthorization())
                        <div class="mt-1 text-xs font-medium text-amber-600">Needs Monnify OTP</div>
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    @if ($w->status === 'pending' || $w->internal_status === 'pending_review')
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('admin.withdrawals.approve', $w) }}">
                                @csrf
                                <input type="hidden" name="confirm_send" value="1">
                                <x-dashboard.button type="submit" size="sm" variant="primary">Approve</x-dashboard.button>
                            </form>
                            <form method="POST" action="{{ route('admin.withdrawals.reject', $w) }}">
                                @csrf
                                <x-dashboard.button type="submit" size="sm" variant="danger">Reject</x-dashboard.button>
                            </form>
                        </div>
                    @elseif ($w->status === 'failed' || $w->internal_status === 'failed')
                        <form method="POST" action="{{ route('admin.withdrawals.retry', $w) }}">
                            @csrf
                            <x-dashboard.button type="submit" size="sm" variant="secondary">Retry payout</x-dashboard.button>
                        </form>
                    @elseif ($w->needsProviderAuthorization())
                        <a href="{{ route('admin.withdrawals.show', $w) }}" class="inline-block">
                            <x-dashboard.button size="sm" variant="primary">Enter Monnify OTP</x-dashboard.button>
                        </a>
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
