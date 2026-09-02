@extends('layouts.dashboard-admin')

@section('title', $tool->resolvedDisplayName())

@section('content')
@php
    use App\Enums\UserToolStatus;

    $isPendingSetup = $tool->status === UserToolStatus::PendingSetup;
    $integration = $tool->integration;
    $fresh = $freshCredentials ?? null;
    $webhookUrl = $integration
        ? url('/webhooks/site-integrations/'.$integration->integration_id)
        : ($fresh['webhook_url'] ?? null);

    $credentialRows = [];
    if ($integration || $fresh) {
        $credentialRows = [
            ['label' => 'Integration ID', 'value' => $fresh['integration_id'] ?? $integration?->integration_id ?? '', 'secret' => false],
            ['label' => 'Client ID', 'value' => $fresh['client_id'] ?? $integration?->client_id ?? '', 'secret' => false],
            ['label' => 'Client Secret', 'value' => $fresh['client_secret'] ?? $integration?->client_secret ?? '', 'secret' => true],
            ['label' => 'Webhook Secret', 'value' => $fresh['webhook_secret'] ?? $integration?->webhook_secret ?? '', 'secret' => true],
            ['label' => 'Webhook URL', 'value' => $fresh['webhook_url'] ?? $webhookUrl ?? '', 'secret' => false],
        ];
        if ($tool->site_url) {
            $credentialRows[] = ['label' => 'Site URL', 'value' => $tool->site_url, 'secret' => false];
        }
    }

    $ownedDocsSubtitle = 'Give these values to the merchant developer for this customer-owned site. '
        .'<a href="'.route('developers.integrations.show', ['path' => 'MERCHANT-GUIDE']).'" class="text-primary hover:underline" target="_blank" rel="noopener">Integration docs</a>';
@endphp
<x-layout.page
    title="{{ $tool->resolvedDisplayName() }}"
    subtitle="{{ $tool->product?->title ?? 'Owned website tool' }} · {{ $user->name }}"
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Users', route('admin.users')],
        [$user->name, route('admin.users.show', $user)],
        ['Tools', route('admin.users.tools', $user)],
        [$tool->resolvedDisplayName(), null],
    ]"
>
    <x-slot:actions>
        @if (! $isPendingSetup && $tool->site_url)
            <form method="POST" action="{{ route('admin.users.tools.check', [$user, $tool]) }}">
                @csrf
                <x-dashboard.button type="submit" variant="secondary" size="sm">Check connection</x-dashboard.button>
            </form>
        @endif
        @if (! $isPendingSetup && $integration)
            <form method="POST" action="{{ route('admin.users.tools.rotate', [$user, $tool]) }}" onsubmit="return confirm('Rotate API credentials? The merchant site env must be updated.');">
                @csrf
                <x-dashboard.button type="submit" variant="secondary" size="sm">Rotate keys</x-dashboard.button>
            </form>
        @endif
    </x-slot:actions>

    @if (session('status'))
        <x-dashboard.alert type="success" class="mb-4">{{ session('status') }}</x-dashboard.alert>
    @endif
    @if (session('warning'))
        <x-dashboard.alert type="warning" class="mb-4">{{ session('warning') }}</x-dashboard.alert>
    @endif
    @if (session('error'))
        <x-dashboard.alert type="danger" class="mb-4">{{ session('error') }}</x-dashboard.alert>
    @endif
    @if ($errors->any())
        <x-dashboard.alert type="danger" class="mb-4">Please fix the highlighted fields below.</x-dashboard.alert>
    @endif

    <div class="mb-6 flex flex-wrap items-center gap-3 text-sm">
        <x-dashboard.badge :status="$tool->status->value" />
        @if ($tool->expires_at)
            <span class="text-text-muted">Expires {{ $tool->expires_at->format('j M Y') }}</span>
        @endif
        @if ($integration)
            <span class="text-text-muted">Connection: <span class="font-medium text-text-secondary">{{ $integration->connection_status ?? 'unchecked' }}</span></span>
        @endif
        <span class="font-mono text-xs text-text-muted">{{ $tool->public_id }}</span>
    </div>

    @if ($isPendingSetup)
        <x-dashboard.card class="mb-6">
            <h3 class="mb-4 text-sm font-semibold text-text-primary">Initial setup</h3>
            <p class="mb-4 text-sm text-text-secondary">Configure this purchased website tool and generate unique owned credentials (never demo credentials).</p>
            <form method="POST" action="{{ route('admin.users.tools.setup', [$user, $tool]) }}" class="space-y-4">
                @csrf
                <x-dashboard.input name="site_url" label="Website URL" type="url" :value="old('site_url', $tool->site_url)" required />
                <x-dashboard.input name="admin_login_url" label="Admin login URL" type="url" :value="old('admin_login_url', $tool->admin_login_url)" required />
                <x-dashboard.input name="admin_email" label="Admin email" type="email" :value="old('admin_email', $tool->admin_email)" required />
                <x-dashboard.input name="admin_password" label="Admin password" type="text" required autocomplete="off" />
                <p class="text-xs text-text-muted">Saving starts the subscription clock and generates provisioning API keys for the merchant site.</p>
                <x-dashboard.button type="submit">Save & generate keys</x-dashboard.button>
            </form>
        </x-dashboard.card>
    @else
        @if (! $integration)
            <x-dashboard.alert type="warning" class="mb-6">
                This tool is active but provisioning credentials are missing. Re-run setup or contact engineering — the merchant cannot install integration keys until this is fixed.
            </x-dashboard.alert>
        @elseif ($credentialRows !== [])
            <x-dashboard.integration-credentials-card
                class="mb-6"
                title="Owned tool credentials"
                :subtitle="$ownedDocsSubtitle"
                :credential-rows="$credentialRows"
                :badge-status="$tool->status->value"
                :show-setup-steps="($integration?->connection_status ?? 'unchecked') !== 'ok'"
                :connection-status="$fresh['connection_status'] ?? $integration?->connection_status ?? 'unchecked'"
                :connection-message="$fresh['connection_message'] ?? $integration?->last_error"
                :connection-ok="$fresh['connection_ok'] ?? null"
            />
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <x-dashboard.card>
                <h3 class="mb-4 text-sm font-semibold text-text-primary">Tool settings</h3>
                <form method="POST" action="{{ route('admin.users.tools.reconfigure', [$user, $tool]) }}" class="space-y-4">
                    @csrf
                    <x-dashboard.input name="site_url" label="Website URL" type="url" :value="old('site_url', $tool->site_url)" required />
                    <x-dashboard.input name="admin_login_url" label="Admin login URL" type="url" :value="old('admin_login_url', $tool->admin_login_url)" required />
                    <x-dashboard.input name="admin_email" label="Admin email" type="email" :value="old('admin_email', $tool->admin_email)" required />
                    <x-dashboard.input name="admin_password" label="Admin password" type="text" autocomplete="off" />
                    <p class="text-xs text-text-muted">Reconfigure updates URLs and admin identity. It does <strong>not</strong> extend the paid subscription. Leave password blank to keep the current one.</p>
                    <x-dashboard.button type="submit">Save reconfiguration</x-dashboard.button>
                </form>
            </x-dashboard.card>

            <x-dashboard.card>
                <h3 class="mb-4 text-sm font-semibold text-text-primary">Subscription expiry</h3>
                <form method="POST" action="{{ route('admin.users.tools.expiry', [$user, $tool]) }}" class="space-y-4">
                    @csrf
                    <x-dashboard.input
                        name="expires_at"
                        label="Expires on"
                        type="date"
                        :value="old('expires_at', $tool->expires_at?->format('Y-m-d'))"
                        required
                    />
                    <p class="text-xs text-text-muted">Adjust the paid window for this tool. Updates merchant subscription sync when the site is connected.</p>
                    <x-dashboard.button type="submit" variant="secondary">Update expiry</x-dashboard.button>
                </form>
            </x-dashboard.card>

            <x-dashboard.card class="lg:col-span-2">
                <h3 class="mb-3 text-sm font-semibold">Connection logs</h3>
                <div class="space-y-2">
                    @forelse ($logs as $log)
                        @php
                            $logIsPendingMerchant = ! $log->ok && (
                                str_contains($log->message, 'unknown_integration')
                                || (($log->payload_summary['error'] ?? null) === 'unknown_integration')
                            );
                        @endphp
                        <div class="rounded-lg border border-border-default px-3 py-2 text-xs">
                            <div class="flex justify-between gap-2">
                                <span class="{{ $log->ok ? 'text-emerald-600' : ($logIsPendingMerchant ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ $log->ok ? 'OK' : ($logIsPendingMerchant ? 'Pending merchant' : 'Fail') }}
                                </span>
                                <span class="text-text-muted">{{ $log->created_at->format('j M Y H:i') }}</span>
                            </div>
                            <p class="mt-1 break-words text-text-secondary">{{ $log->message }}</p>
                            @if ($log->http_status)
                                <p class="mt-1 text-[11px] text-text-muted">HTTP {{ $log->http_status }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-text-muted">No checks yet. Install credentials on the merchant site, then click <strong>Check connection</strong>.</p>
                    @endforelse
                </div>
            </x-dashboard.card>
        </div>
    @endif
</x-layout.page>
@endsection
