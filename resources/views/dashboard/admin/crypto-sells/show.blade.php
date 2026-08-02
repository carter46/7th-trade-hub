@extends('layouts.dashboard-admin')

@section('title', 'Verify Crypto Sell #'.$request->id)

@section('content')
@php
    $shortfall = $incoming
        ? max(0, (float) $request->amount_crypto - (float) $incoming->amount)
        : null;
    $excess = $incoming
        ? max(0, (float) $incoming->amount - (float) $request->amount_crypto)
        : null;
@endphp
<x-layout.page
    title="Verify order #{{ $request->id }}"
    subtitle="Checklist must pass before Approve & credit. Credit uses the locked quote."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Crypto sells', route('admin.crypto-sells')],
        ['#'.$request->id, null],
    ]"
>
    <div class="space-y-4" x-data="{
        network: true,
        destination: true,
        amount: {{ $request->amount_match_status === 'exact' || $request->amount_match_status === null ? 'true' : 'false' }},
        confirmations: {{ $confirmationsReady ? 'true' : 'false' }},
        valid: true,
        get ready() { return this.network && this.destination && this.amount && this.confirmations && this.valid; }
    }">
        <x-dashboard.card>
            <div class="flex flex-wrap gap-4 justify-between">
                <div>
                    <p class="text-sm text-text-muted">Customer</p>
                    <p class="font-semibold">{{ \App\Models\User::labelFor($request->user) }}</p>
                </div>
                <div>
                    <p class="text-sm text-text-muted">Status</p>
                    <x-dashboard.badge :status="$request->status" />
                </div>
                <div>
                    <p class="text-sm text-text-muted">Locked payout</p>
                    <p class="font-display text-xl font-semibold">₦{{ number_format((float) $request->expected_ngn, 2) }}</p>
                </div>
            </div>
            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                <div><dt class="text-text-muted">Expected</dt><dd>{{ $request->amount_crypto }} {{ $request->coin }} ({{ $request->network }})</dd></div>
                <div><dt class="text-text-muted">USD</dt><dd>${{ number_format((float) $request->amount_usd, 2) }}</dd></div>
                <div><dt class="text-text-muted">Rate snapshot</dt><dd>Market ₦{{ $request->market_rate_ngn }} − {{ $request->spread_ngn }} = ₦{{ $request->quoted_rate_ngn }}</dd></div>
                <div><dt class="text-text-muted">Deposit address</dt><dd class="font-mono text-xs break-all">{{ $request->platform_address }}</dd></div>
                <div>
                    <dt class="text-text-muted">Confirmations</dt>
                    <dd>
                        {{ $observed }} / {{ $required }}
                        @if($confirmationsReady)
                            <x-dashboard.badge status="success">Ready</x-dashboard.badge>
                        @else
                            <x-dashboard.badge status="warning">Waiting</x-dashboard.badge>
                        @endif
                    </dd>
                </div>
                <div><dt class="text-text-muted">Amount match</dt><dd>{{ $request->amount_match_status ?: '—' }}</dd></div>
            </dl>
        </x-dashboard.card>

        <x-dashboard.card>
            <h2 class="text-base font-semibold mb-3">On-chain deposit</h2>
            @if($incoming)
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-text-muted">Received</dt><dd>{{ $incoming->amount }} {{ $incoming->coin }}</dd></div>
                    <div><dt class="text-text-muted">Block height</dt><dd>{{ $incoming->block_height ?? '—' }}</dd></div>
                    <div><dt class="text-text-muted">TX</dt><dd class="font-mono text-xs break-all">{{ $incoming->tx_hash }}</dd></div>
                    <div>
                        @if($explorerUrl)
                            <a href="{{ $explorerUrl }}" target="_blank" rel="noopener" class="text-primary text-sm underline">Open in explorer</a>
                        @endif
                    </div>
                    @if($shortfall > 0)
                        <div class="sm:col-span-2 text-warning text-sm">Underpaid by {{ number_format($shortfall, 8) }} {{ $request->coin }}. Status: waiting customer.</div>
                    @endif
                    @if($excess > 0)
                        <div class="sm:col-span-2 text-warning text-sm">Overpaid by {{ number_format($excess, 8) }} {{ $request->coin }}. Review before credit.</div>
                    @endif
                </dl>
            @else
                <p class="text-sm text-text-muted">No matched incoming deposit yet. Poller will fill this when funds arrive, or paste a TX hash below.</p>
            @endif
        </x-dashboard.card>

        @if($request->isApprovable())
            <x-dashboard.card>
                <h2 class="text-base font-semibold mb-3">Verification checklist</h2>
                <form method="POST" action="{{ route('admin.crypto-sells.approve', $request) }}" class="space-y-3">
                    @csrf
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="checklist_network" value="1" x-model="network" required> Correct network</label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="checklist_destination" value="1" x-model="destination" required> Correct destination wallet</label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="checklist_amount" value="1" x-model="amount" required> Amount verified</label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="checklist_confirmations" value="1" x-model="confirmations" required> Confirmations met</label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="checklist_valid" value="1" x-model="valid" required> Transaction valid</label>

                    <x-dashboard.input name="tx_hash" label="Transaction hash" :value="old('tx_hash', $request->tx_hash ?? $incoming?->tx_hash)" required />
                    @if(in_array($request->status, ['underpaid_waiting_customer', 'overpaid_review'], true))
                        <x-dashboard.input name="credit_ngn_override" label="Credit NGN override (optional)" type="number" step="0.01" :value="old('credit_ngn_override', $request->expected_ngn)" hint="Leave blank to credit locked expected_ngn." />
                    @endif
                    <x-dashboard.input name="admin_notes" label="Notes" :value="old('admin_notes', $request->admin_notes)" />

                    <div class="flex flex-wrap gap-2">
                        <x-dashboard.button type="submit" variant="primary" x-bind:disabled="!ready">Approve &amp; credit NGN</x-dashboard.button>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.crypto-sells.reject', $request) }}" class="mt-4 space-y-2">
                    @csrf
                    <x-dashboard.input name="notes" label="Reject notes" />
                    <x-dashboard.button type="submit" variant="danger" size="sm">Reject</x-dashboard.button>
                </form>
            </x-dashboard.card>
        @endif
    </div>
</x-layout.page>
@endsection
