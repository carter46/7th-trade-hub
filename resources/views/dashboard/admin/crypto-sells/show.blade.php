@extends('layouts.dashboard-admin')

@section('title', 'Verify '.($request->tracking_code ?: '#'.$request->id))

@section('content')
@php
    $user = $request->user;
    $trust = $customerTrust ?? ['approved' => 0, 'rejected' => 0, 'total' => 0, 'first' => true, 'disputes' => 0];
    $stars = min(5, max(1, (int) round(1 + min(4, ($trust['approved'] ?? 0) / 5))));
    if (($trust['first'] ?? true) && ($trust['approved'] ?? 0) === 0) {
        $stars = 0;
    }
@endphp
<x-layout.page
    title="{{ $request->tracking_code ?: 'Order #'.$request->id }}"
    subtitle="OTC verification desk — checklist required before credit."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Crypto sells', route('admin.crypto-sells')],
        [$request->tracking_code ?: '#'.$request->id, null],
    ]"
>
    <x-slot:actions>
        @if($user)
            <x-dashboard.button :href="route('admin.users.show', $user)" variant="secondary" size="sm">View customer</x-dashboard.button>
            <x-dashboard.button :href="route('admin.tickets.create', ['user_id' => $user->id])" variant="secondary" size="sm">Message customer</x-dashboard.button>
        @endif
        @if($depositWallet ?? null)
            <x-dashboard.button :href="route('admin.crypto-wallets.edit', $depositWallet)" variant="secondary" size="sm">View wallet</x-dashboard.button>
        @endif
    </x-slot:actions>

    <div class="space-y-4" x-data="{
        network: false,
        destination: false,
        amount: {{ (($request->amount_match_status === 'exact') || (($differenceLabel ?? '') === 'Exact')) ? 'true' : 'false' }},
        confirmations: {{ !empty($confirmationsReady) ? 'true' : 'false' }},
        valid: false,
        get ready() { return this.network && this.destination && this.amount && this.confirmations && this.valid; }
    }">
        {{-- Customer trust --}}
        <x-dashboard.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-text-muted">Customer</p>
                    @if($user)
                        <a href="{{ route('admin.users.show', $user) }}" class="text-lg font-semibold text-text-primary hover:underline">
                            {{ \App\Models\User::labelFor($user) }}
                        </a>
                    @else
                        <p class="text-lg font-semibold">Unknown</p>
                    @endif
                    <p class="mt-1 text-sm text-warning tracking-tight">
                        @if($stars === 0)
                            <span class="text-text-muted">No rating yet</span>
                        @else
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= $stars ? 'text-warning' : 'text-text-muted/40' }}">★</span>
                            @endfor
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-text-secondary">
                        {{ (int) ($trust['approved'] ?? 0) }} successful OTC sell{{ ($trust['approved'] ?? 0) === 1 ? '' : 's' }}
                    </p>
                    <p class="text-xs text-text-muted">
                        @if($trust['first'] ?? true)
                            First OTC transaction
                        @elseif(($trust['disputes'] ?? 0) === 0)
                            No previous disputes
                        @else
                            {{ (int) $trust['disputes'] }} previous rejected sell{{ $trust['disputes'] === 1 ? '' : 's' }}
                        @endif
                    </p>
                </div>
                <div class="text-right space-y-1">
                    <x-dashboard.badge :status="$request->status" />
                    <p class="text-xs text-text-muted capitalize">Stage: {{ str_replace('_', ' ', $stage ?? $request->status) }}</p>
                </div>
            </div>
        </x-dashboard.card>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            {{-- Order summary --}}
            <x-dashboard.card>
                <h2 class="text-sm font-semibold text-text-primary mb-3">Order Summary</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-text-muted">Order ID</dt><dd class="font-mono text-xs">{{ $request->tracking_code ?: '#'.$request->id }}</dd></div>
                    <div><dt class="text-text-muted">Internal #</dt><dd>{{ $request->id }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="text-text-muted">Customer</dt>
                        <dd>
                            @if($user)
                                <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-primary hover:underline">{{ \App\Models\User::labelFor($user) }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div><dt class="text-text-muted">Status</dt><dd><x-dashboard.badge :status="$request->status" /></dd></div>
                    <div><dt class="text-text-muted">Current stage</dt><dd class="capitalize">{{ str_replace('_', ' ', $stage ?? '—') }}</dd></div>
                    <div><dt class="text-text-muted">Created</dt><dd>{{ $request->created_at?->format('Y-m-d H:i') }}</dd></div>
                    <div><dt class="text-text-muted">Quote expiry</dt><dd>{{ $request->expires_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                </dl>
            </x-dashboard.card>

            {{-- Deposit information --}}
            <x-dashboard.card>
                <h2 class="text-sm font-semibold text-text-primary mb-3">Deposit Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-text-muted">Coin</dt><dd class="font-semibold">{{ $request->coin }}</dd></div>
                    <div><dt class="text-text-muted">Network</dt><dd class="font-semibold">{{ $networkLabel ?? $request->network }}</dd></div>
                    <div><dt class="text-text-muted">Expected crypto</dt><dd>{{ $expectedCrypto ?? $request->amount_crypto }} {{ $request->coin }}</dd></div>
                    <div><dt class="text-text-muted">Received crypto</dt><dd>{{ $receivedCrypto !== null ? $receivedCrypto.' '.$request->coin : '—' }}</dd></div>
                    <div>
                        <dt class="text-text-muted">Difference</dt>
                        <dd>
                            <span @class([
                                'font-medium',
                                'text-success' => ($differenceLabel ?? '') === 'Exact',
                                'text-warning' => in_array($differenceLabel ?? '', ['Under', 'Over'], true),
                            ])>{{ $differenceLabel ?? '—' }}</span>
                            @if(isset($difference) && $difference !== null && abs($difference) >= 1e-12)
                                <span class="text-xs text-text-muted">({{ number_format($difference, 8) }})</span>
                            @endif
                        </dd>
                    </div>
                    <div><dt class="text-text-muted">USD value</dt><dd>${{ number_format((float) $request->amount_usd, 2) }}</dd></div>
                    <div><dt class="text-text-muted">Locked NGN payout</dt><dd class="font-display text-base font-semibold">₦{{ number_format((float) $request->expected_ngn, 2) }}</dd></div>
                    <div><dt class="text-text-muted">Locked rate</dt><dd>₦{{ number_format((float) $request->quoted_rate_ngn, 2) }} /$</dd></div>
                </dl>
            </x-dashboard.card>

            {{-- Blockchain --}}
            <x-dashboard.card>
                <h2 class="text-sm font-semibold text-text-primary mb-3">Blockchain Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="sm:col-span-2">
                        <dt class="text-text-muted">TX hash</dt>
                        <dd class="font-mono text-xs break-all">{{ $request->tx_hash ?? $incoming?->tx_hash ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Explorer</dt>
                        <dd>
                            @if($explorerUrl)
                                <a href="{{ $explorerUrl }}" target="_blank" rel="noopener" class="inline-flex rounded-lg border border-border-default px-3 py-1.5 text-sm font-medium text-primary hover:bg-muted/40">Open explorer</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div><dt class="text-text-muted">Block height</dt><dd>{{ $incoming?->block_height ?? '—' }}</dd></div>
                    <div>
                        <dt class="text-text-muted">Confirmations</dt>
                        <dd>
                            {{ $observed }} / {{ $required }}
                            @if(!empty($confirmationsReady))
                                <x-dashboard.badge status="success">Ready</x-dashboard.badge>
                            @else
                                <x-dashboard.badge status="warning">Waiting</x-dashboard.badge>
                            @endif
                        </dd>
                    </div>
                    <div><dt class="text-text-muted">Detection time</dt><dd>{{ $incoming?->detected_at?->format('Y-m-d H:i:s') ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-text-muted">From address</dt><dd class="font-mono text-xs break-all">{{ $incoming?->from_address ?? '—' }}</dd></div>
                </dl>
            </x-dashboard.card>

            {{-- Destination wallet --}}
            <x-dashboard.card>
                <h2 class="text-sm font-semibold text-text-primary mb-3">Destination Wallet</h2>
                @if($depositWallet ?? null)
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-text-muted">Label</dt><dd class="font-semibold">{{ $depositWallet->label ?: ($depositWallet->coin.' Wallet '.$depositWallet->id) }}</dd></div>
                        <div><dt class="text-text-muted">Purpose</dt><dd>{{ $depositWallet->purpose ?: 'OTC Deposits' }}</dd></div>
                        <div><dt class="text-text-muted">Owner</dt><dd>{{ $depositWallet->owner ?: '—' }}</dd></div>
                        <div><dt class="text-text-muted">Status</dt><dd>{{ $depositWallet->is_active ? 'Active' : 'Inactive' }}</dd></div>
                        <div><dt class="text-text-muted">Open orders</dt><dd>{{ $openOnWallet ?? 0 }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-text-muted">Address</dt><dd class="font-mono text-xs break-all">{{ $depositWallet->address }}</dd></div>
                    </dl>
                    <div class="mt-3">
                        <x-dashboard.button :href="route('admin.crypto-wallets.edit', $depositWallet)" size="sm" variant="secondary">Open wallet</x-dashboard.button>
                    </div>
                @else
                    <p class="text-sm text-text-muted">No linked deposit wallet record.</p>
                    <p class="mt-2 font-mono text-xs break-all">{{ $request->platform_address }}</p>
                @endif
            </x-dashboard.card>
        </div>

        @if($request->isApprovable())
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <x-dashboard.card>
                    <h2 class="text-sm font-semibold text-text-primary mb-3">Verification Checklist</h2>
                    <form method="POST" action="{{ route('admin.crypto-sells.approve', $request) }}" class="space-y-3">
                        @csrf
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="checklist_network" value="1" x-model="network" required> Correct network</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="checklist_destination" value="1" x-model="destination" required> Correct destination</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="checklist_amount" value="1" x-model="amount" required> Amount verified</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="checklist_confirmations" value="1" x-model="confirmations" required> Confirmations met</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="checklist_valid" value="1" x-model="valid" required> Transaction valid</label>

                        <x-dashboard.input name="tx_hash" label="Transaction hash" :value="old('tx_hash', $request->tx_hash ?? $incoming?->tx_hash)" required />
                        @if(in_array($request->status, ['underpaid_waiting_customer', 'overpaid_review'], true))
                            <x-dashboard.input name="credit_ngn_override" label="Credit NGN override (optional)" type="number" step="0.01" :value="old('credit_ngn_override', $request->expected_ngn)" hint="Leave blank to credit locked expected_ngn." />
                        @endif
                        <x-dashboard.input name="admin_notes" label="Notes" :value="old('admin_notes', $request->admin_notes)" />

                        <x-dashboard.button type="submit" variant="primary" x-bind:disabled="!ready">Approve &amp; credit NGN</x-dashboard.button>
                    </form>
                </x-dashboard.card>

                <x-dashboard.card>
                    <h2 class="text-sm font-semibold text-text-primary mb-3">Admin Actions</h2>
                    <div class="flex flex-wrap gap-2 mb-4">
                        @if($user)
                            <x-dashboard.button :href="route('admin.tickets.create', ['user_id' => $user->id])" variant="secondary" size="sm">Message customer</x-dashboard.button>
                            <x-dashboard.button :href="route('admin.users.show', $user)" variant="secondary" size="sm">View customer</x-dashboard.button>
                        @endif
                        @if($depositWallet ?? null)
                            <x-dashboard.button :href="route('admin.crypto-wallets.edit', $depositWallet)" variant="secondary" size="sm">View wallet</x-dashboard.button>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.crypto-sells.reject', $request) }}" class="space-y-2 border-t border-border-subtle pt-4">
                        @csrf
                        <x-dashboard.input name="notes" label="Reject notes" required />
                        <x-dashboard.button type="submit" variant="danger" size="sm">Reject</x-dashboard.button>
                    </form>
                </x-dashboard.card>
            </div>
        @endif
    </div>
</x-layout.page>
@endsection
