@extends('layouts.dashboard-admin')

@section('title', 'Blockchain Monitoring')

@section('content')
@php
    $blockchain = $blockchain ?? \App\Models\IntegrationProvider::forProvider(\App\Models\IntegrationProvider::BLOCKCHAIN_MONITORING);
    $chainMeta = $blockchain->meta ?? [];
    $health = $chainMeta['network_health'] ?? [];
    $monitoredNetworks = config('crypto.monitored_networks', []);
    $monitorProviders = config('crypto.monitor_providers', []);
    $selectedProvider = old('monitor_provider', $chainMeta['monitor_provider'] ?? 'native');
    if (! ($monitorProviders[$selectedProvider]['enabled'] ?? false)) {
        $selectedProvider = 'native';
    }
    $catalog = app(\App\Modules\Wallet\Services\Blockchain\MonitoredNetworkCatalog::class);
    $registry = app(\App\Modules\Wallet\Services\Blockchain\ExplorerClientRegistry::class);
@endphp
<x-layout.page
    title="Blockchain Monitoring"
    subtitle="Explorer providers and network health for crypto deposit detection."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['System', null],
        ['Blockchain Monitoring', null],
    ]"
>
    <div class="space-y-6">
        <x-dashboard.card variant="solid" id="blockchain-monitoring">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Supported networks</h2>
            <p class="text-sm text-text-secondary mb-4">
                Deposit detection by blockchain network (not coin). Credentials are stored here — not in .env.
            </p>
            @error('blockchain_test')
                <p class="mb-3 text-sm text-danger">{{ $message }}</p>
            @enderror

            <div class="mb-5 overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="text-left text-xs text-text-muted border-b border-border-default">
                            <th class="py-2 pr-3 font-medium">Network</th>
                            <th class="py-2 pr-3 font-medium">Status</th>
                            <th class="py-2 pr-3 font-medium">Provider</th>
                            <th class="py-2 pr-3 font-medium">Last poll</th>
                            <th class="py-2 pr-3 font-medium">Last success</th>
                            <th class="py-2 pr-3 font-medium">Last error</th>
                            <th class="py-2 pr-3 font-medium">Wallets</th>
                            <th class="py-2 font-medium">Tip / latency</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($monitoredNetworks as $netId => $netDef)
                            @php
                                $h = $health[$netId] ?? [];
                                $status = $h['status'] ?? 'idle';
                                try {
                                    $resolved = $registry->resolve($netId);
                                    $providerLabel = $catalog->displayProvider($netId, $resolved['provider'], $resolved['client_key']);
                                } catch (\Throwable) {
                                    $providerLabel = $h['provider'] ?? '—';
                                }
                                if (!empty($h['provider']) && ($h['client'] ?? null)) {
                                    $providerLabel = $catalog->displayProvider($netId, (string) $h['provider'], (string) $h['client']);
                                }
                                $statusClass = match ($status) {
                                    'healthy' => 'text-success',
                                    'error' => 'text-danger',
                                    default => 'text-text-muted',
                                };
                            @endphp
                            <tr class="border-b border-border-subtle align-top">
                                <td class="py-2.5 pr-3 font-medium text-text-primary whitespace-nowrap">{{ $netDef['label'] ?? $netId }}</td>
                                <td class="py-2.5 pr-3 {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                                <td class="py-2.5 pr-3 text-text-secondary whitespace-nowrap">{{ $providerLabel }}</td>
                                <td class="py-2.5 pr-3 text-xs text-text-muted whitespace-nowrap">
                                    {{ !empty($h['last_poll_at']) ? \Illuminate\Support\Carbon::parse($h['last_poll_at'])->diffForHumans() : '—' }}
                                </td>
                                <td class="py-2.5 pr-3 text-xs text-text-muted whitespace-nowrap">
                                    {{ !empty($h['last_success_at']) ? \Illuminate\Support\Carbon::parse($h['last_success_at'])->diffForHumans() : '—' }}
                                </td>
                                <td class="py-2.5 pr-3 text-xs text-danger max-w-[12rem]">
                                    {{ !empty($h['last_error']) ? \Illuminate\Support\Str::limit($h['last_error'], 80) : '—' }}
                                </td>
                                <td class="py-2.5 pr-3 text-xs text-text-secondary whitespace-nowrap">
                                    {{ (int) ($h['wallets_active'] ?? 0) }} active / {{ (int) ($h['wallets_disabled'] ?? 0) }} disabled
                                </td>
                                <td class="py-2.5 text-xs text-text-muted whitespace-nowrap">
                                    @if(isset($h['tip_height']))
                                        #{{ number_format((int) $h['tip_height']) }}
                                    @else
                                        —
                                    @endif
                                    @if(isset($h['latency_ms']))
                                        · {{ (int) $h['latency_ms'] }} ms
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-text-muted mb-4">
                Last poll: {{ !empty($chainMeta['last_poll_at']) ? \Illuminate\Support\Carbon::parse($chainMeta['last_poll_at'])->diffForHumans() : '—' }}
                · Status: {{ $blockchain->status ?: 'idle' }}
            </p>

            <form method="POST" action="{{ route('admin.blockchain-monitoring.update') }}" class="space-y-4" x-data="{ provider: @js($selectedProvider) }">
                @csrf
                <input type="hidden" name="blockchain_enabled" value="0">
                <x-dashboard.toggle name="blockchain_enabled" label="Enable deposit monitoring" :checked="old('blockchain_enabled', $blockchain->enabled)" value="1" />

                <fieldset>
                    <legend class="text-sm font-medium text-text-primary mb-2">Provider</legend>
                    <div class="space-y-2">
                        @foreach ($monitorProviders as $providerId => $providerDef)
                            @php $enabled = (bool) ($providerDef['enabled'] ?? false); @endphp
                            <label class="flex items-start gap-2 {{ $enabled ? 'cursor-pointer' : 'opacity-60 cursor-not-allowed' }}">
                                <input
                                    type="radio"
                                    name="monitor_provider"
                                    value="{{ $providerId }}"
                                    class="mt-1"
                                    @checked($selectedProvider === $providerId)
                                    @disabled(! $enabled)
                                    @if($enabled) x-model="provider" @endif
                                >
                                <span>
                                    <span class="text-sm font-medium text-text-primary">
                                        {{ $providerDef['label'] ?? $providerId }}
                                        @unless($enabled)
                                            <span class="text-text-muted font-normal">(Coming soon)</span>
                                        @endunless
                                    </span>
                                    <span class="block text-xs text-text-muted">{{ $providerDef['description'] ?? '' }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div x-show="provider === 'blockchain_com'" x-cloak>
                        <x-dashboard.input
                            name="blockchain_com_api_key"
                            type="password"
                            label="Explorer API key"
                            value=""
                            hint="Leave blank to keep.{{ filled($blockchain->credential('blockchain_com_api_key')) ? ' Key stored.' : '' }} Header: X-Explorer-Auth-Key. Covers Bitcoin, Ethereum, Solana."
                            autocomplete="new-password"
                        />
                    </div>
                    <div>
                        <x-dashboard.input
                            name="etherscan_api_key"
                            type="password"
                            label="EVM explorer API key"
                            value=""
                            hint="Leave blank to keep.{{ filled($blockchain->credential('etherscan_api_key')) ? ' Key stored.' : '' }} Ethereum / BNB / Polygon / Base / Arbitrum (and ERC-20 feeds)."
                            autocomplete="new-password"
                        />
                    </div>
                    <div>
                        <x-dashboard.input
                            name="trongrid_api_key"
                            type="password"
                            label="TRON API key"
                            value=""
                            hint="Leave blank to keep.{{ filled($blockchain->credential('trongrid_api_key')) ? ' Key stored.' : '' }} Always used for TRC20 (Blockchain.com has no TRON)."
                            autocomplete="new-password"
                        />
                    </div>
                    <div x-show="provider === 'native'" x-cloak>
                        <x-dashboard.input
                            name="solana_rpc_url"
                            label="Solana RPC URL (optional)"
                            :value="old('solana_rpc_url', $blockchain->credential('solana_rpc_url'))"
                        />
                    </div>
                    <div class="rounded-lg border border-border-subtle px-3 py-2 text-sm text-text-secondary" x-show="provider === 'native'" x-cloak>
                        <p class="font-medium text-text-primary">Bitcoin</p>
                        <p class="text-xs text-text-muted mt-0.5">Public explorer — no API key required.</p>
                    </div>
                    <p class="text-xs text-text-muted md:col-span-2" x-show="provider === 'blockchain_com'" x-cloak>
                        Other EVM networks (BNB, Polygon, Base, Arbitrum) fall back to the EVM explorer key above.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Poll interval</label>
                    <select name="poll_interval_minutes" class="w-full max-w-xs rounded-lg border border-border-subtle bg-surface px-3 py-2 text-sm">
                        <option value="1" @selected((int) old('poll_interval_minutes', $chainMeta['poll_interval_minutes'] ?? 1) === 1)>Every 1 minute</option>
                        <option value="2" @selected((int) old('poll_interval_minutes', $chainMeta['poll_interval_minutes'] ?? 1) === 2)>Every 2 minutes</option>
                    </select>
                </div>
                <p class="text-xs text-text-muted">Scheduler runs <code>crypto:poll-deposits</code> every minute.</p>
                <x-dashboard.button type="submit" variant="primary">Save blockchain settings</x-dashboard.button>
            </form>
            <form method="POST" action="{{ route('admin.blockchain-monitoring.test') }}" class="mt-4 flex flex-wrap gap-2 items-end">
                @csrf
                <div>
                    <label class="block text-xs mb-1">Test network</label>
                    <select name="network" class="rounded-lg border border-border-subtle bg-surface px-3 py-2 text-sm">
                        @foreach ($monitoredNetworks as $netId => $netDef)
                            <option value="{{ $netId }}">{{ $netDef['label'] ?? $netId }}</option>
                        @endforeach
                    </select>
                </div>
                <x-dashboard.button type="submit" variant="secondary">Test connection</x-dashboard.button>
            </form>
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
