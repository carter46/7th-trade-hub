@extends('layouts.dashboard-admin')

@section('title', 'Payment Settings')

@section('content')
@php
    $monnifyMeta = $monnify->meta ?? [];
    $manualBank = $manualBankTransfer ?? [];
@endphp
<x-layout.page
    title="Payment Settings"
    subtitle="Gateway credentials and manual bank transfer for order checkout."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Site Settings', route('admin.settings')],
        ['Payments', null],
    ]"
>
    <div class="space-y-6">
        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Monnify (payments & payouts)</h2>
            <p class="text-sm text-text-secondary mb-4">Checkout deposits, reserved accounts, name enquiry, and disbursements. Credentials are stored here — not in <code>.env</code>.</p>
            @error('monnify_test')
                <p class="mb-4 text-sm text-danger">{{ $message }}</p>
            @enderror
            <div class="mb-4 grid gap-3 sm:grid-cols-3 text-sm">
                <div>
                    <p class="text-text-muted">Status</p>
                    <p class="mt-1 font-medium text-text-primary capitalize">{{ $monnify->status ?: 'idle' }}</p>
                </div>
                <div>
                    <p class="text-text-muted">Webhook URL</p>
                    <input type="text" readonly value="{{ $monnifyWebhookUrl }}" class="mt-1 w-full rounded-lg border border-border-subtle bg-surface-muted px-3 py-2 text-xs text-text-secondary" onclick="this.select()" />
                </div>
                <div>
                    <p class="text-text-muted">Ops</p>
                    <p class="mt-1 text-text-secondary text-xs">Whitelist server IP with Monnify (D06). Enable disbursements. Prefer MFA disabled for Approve &amp; Send.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.settings.monnify') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="monnify_enabled" value="0">
                <x-dashboard.toggle name="monnify_enabled" label="Enable Monnify" :checked="old('monnify_enabled', $monnify->enabled)" value="1" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-dashboard.secret-input
                        name="monnify_api_key"
                        label="API Key"
                        :stored="$monnify->credential('api_key')"
                    />
                    <x-dashboard.secret-input
                        name="monnify_secret_key"
                        label="Secret Key"
                        :stored="$monnify->credential('secret_key')"
                    />
                    <x-dashboard.input name="monnify_contract_code" label="Contract Code" :value="old('monnify_contract_code', $monnify->credential('contract_code'))" />
                    <x-dashboard.input name="monnify_wallet_account_number" label="Disbursement wallet account number" :value="old('monnify_wallet_account_number', $monnify->credential('wallet_account_number'))" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-primary mb-1">Environment</label>
                    <select name="monnify_environment" class="w-full rounded-lg border border-border-subtle bg-surface px-3 py-2 text-sm">
                        <option value="sandbox" @selected(old('monnify_environment', $monnifyMeta['environment'] ?? 'sandbox') === 'sandbox')>Sandbox</option>
                        <option value="live" @selected(old('monnify_environment', $monnifyMeta['environment'] ?? 'sandbox') === 'live')>Live</option>
                    </select>
                </div>
                @php
                    $webhookIps = old(
                        'monnify_webhook_allowed_ips',
                        is_array($monnifyMeta['webhook_allowed_ips'] ?? null)
                            ? implode(', ', $monnifyMeta['webhook_allowed_ips'])
                            : '35.242.133.146'
                    );
                @endphp
                <x-dashboard.input
                    name="monnify_webhook_allowed_ips"
                    label="Live webhook allowed IPs"
                    :value="$webhookIps"
                    hint="Comma-separated. Live webhooks from other IPs are rejected. Default: 35.242.133.146"
                    autocomplete="off"
                />
                <input type="hidden" name="monnify_reserved_accounts_without_kyc" value="0">
                <x-dashboard.toggle name="monnify_reserved_accounts_without_kyc" label="Allow reserved accounts when KYC is off" :checked="old('monnify_reserved_accounts_without_kyc', $monnifyMeta['reserved_accounts_without_kyc'] ?? false)" value="1" />
                <x-dashboard.button type="submit" variant="primary">Save Monnify settings</x-dashboard.button>
            </form>
            <form
                method="POST"
                action="{{ route('admin.settings.monnify.test') }}"
                class="mt-4 space-y-2"
                x-data="{
                    testing: false,
                    status: '',
                    ok: null,
                    async submit(e) {
                        e.preventDefault();
                        if (this.testing) return;
                        this.testing = true;
                        this.status = '';
                        this.ok = null;
                        try {
                            const res = await fetch(e.target.action, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': e.target.querySelector('[name=_token]')?.value || '',
                                },
                                body: new FormData(e.target),
                            });
                            const data = await res.json().catch(() => ({}));
                            this.ok = !!data.ok;
                            this.status = data.message || data.errors?.monnify_test?.[0] || (this.ok ? 'OK' : 'Failed');
                        } catch (err) {
                            this.ok = false;
                            this.status = err?.message || 'Failed';
                        } finally {
                            this.testing = false;
                        }
                    }
                }"
                @submit="submit"
            >
                @csrf
                <x-dashboard.button type="submit" variant="secondary" x-bind:disabled="testing">
                    <span x-text="testing ? 'Testing…' : 'Test connection'">Test connection</span>
                </x-dashboard.button>
                <p class="text-sm break-words" x-show="status" x-text="status" x-cloak :class="ok === true ? 'text-success' : 'text-danger'"></p>
            </form>
        </x-dashboard.card>

        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Manual bank transfer (order payments)</h2>
            <p class="text-sm text-text-secondary mb-4">
                Let customers pay for platform services by bank transfer at checkout. Not used for wallet funding — wallet top-ups are gateway-only.
            </p>
            @if(empty($manualBankTransferSaveUrl))
                <x-dashboard.alert type="warning">
                    Manual bank transfer save route is not registered. Run <code class="text-xs">php artisan route:clear</code> after deploy, then reload.
                </x-dashboard.alert>
            @else
            <form method="POST" action="{{ $manualBankTransferSaveUrl }}" class="space-y-4">
                @csrf
                <input type="hidden" name="manual_bank_transfer_enabled" value="0">
                <x-dashboard.toggle
                    name="manual_bank_transfer_enabled"
                    label="Enable manual bank transfer at checkout"
                    :checked="old('manual_bank_transfer_enabled', $manualBankTransferEnabled ?? false)"
                    value="1"
                />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-dashboard.input
                        name="manual_bank_transfer_bank_name"
                        label="Bank name"
                        :value="old('manual_bank_transfer_bank_name', $manualBank['bank_name'] ?? '')"
                    />
                    <x-dashboard.input
                        name="manual_bank_transfer_account_number"
                        label="Account number"
                        :value="old('manual_bank_transfer_account_number', $manualBank['account_number'] ?? '')"
                    />
                    <x-dashboard.input
                        name="manual_bank_transfer_account_name"
                        label="Account name"
                        class="sm:col-span-2"
                        :value="old('manual_bank_transfer_account_name', $manualBank['account_name'] ?? '')"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-primary mb-1">Transfer instructions</label>
                    <textarea
                        name="manual_bank_transfer_instructions"
                        rows="4"
                        class="w-full rounded-lg border border-border-subtle bg-surface px-3 py-2 text-sm text-text-primary"
                    >{{ old('manual_bank_transfer_instructions', $manualBank['instructions'] ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-text-muted">Shown on the pending payment page after checkout.</p>
                </div>
                <x-dashboard.button type="submit" variant="primary">Save manual bank transfer settings</x-dashboard.button>
            </form>
            @endif
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
