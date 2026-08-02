@php
    $wallet = $wallet ?? null;
    $catalogCoins = $catalogCoins ?? [];
    $networksByCoin = $networksByCoin ?? [];
    $usedByByNetwork = $usedByByNetwork ?? [];
    $defaultConfirmationsByNetwork = $defaultConfirmationsByNetwork ?? [];
    $supportedNetworkLabels = $supportedNetworkLabels ?? [];
    $unsupportedCoins = $unsupportedCoins ?? [];
    $coinOptions = array_values(array_keys($networksByCoin));
    $selectedCoin = old('coin', $wallet->coin ?? ($coinOptions[0] ?? ''));
    $selectedNetwork = old('network', $wallet
        ? app(\App\Modules\Wallet\Services\NetworkRegistry::class)->resolveId((string) $wallet->network)
        : '');
    $selectedConfs = old('required_confirmations', $wallet->required_confirmations ?? null);
    $logos = [];
    foreach ($catalogCoins as $row) {
        $sym = strtoupper((string) ($row['symbol'] ?? ''));
        if ($sym !== '') {
            $logos[$sym] = $row['logo'] ?? null;
        }
    }
@endphp
<div
    class="grid grid-cols-1 md:grid-cols-2 gap-4"
    x-data="{
        coin: @js($selectedCoin),
        network: @js($selectedNetwork),
        map: @js($networksByCoin),
        logos: @js($logos),
        usedBy: @js($usedByByNetwork),
        defaultConfs: @js($defaultConfirmationsByNetwork),
        confs: @js($selectedConfs),
        showUsedBy: false,
        get networks() { return this.map[this.coin] || []; },
        get logo() { return this.logos[this.coin] || null; },
        get usedByRow() { return this.usedBy[this.network] || { count: 0, coins: [], label: '' }; },
        syncNetwork(forceConfs = false) {
            const ids = this.networks.map((n) => n.id);
            const changed = ! ids.includes(this.network);
            if (changed) {
                this.network = ids[0] || '';
            }
            this.syncConfs(forceConfs || changed);
        },
        syncConfs(force = false) {
            if (force || ! this.confs) {
                this.confs = this.defaultConfs[this.network] || 12;
            }
        }
    }"
    x-init="syncNetwork(false)"
>
    @if ($coinOptions === [])
        <div class="md:col-span-2 rounded-xl border border-warning/40 bg-warning/10 px-3 py-3 text-sm text-text-secondary space-y-2">
            <p>
                No OTC-ready coins in
                <a href="{{ route('admin.exchange-rates') }}" class="font-medium text-primary underline-offset-2 hover:underline">Coin Catalog</a>.
                A coin needs at least one monitorable deposit network.
            </p>
            @if ($supportedNetworkLabels !== [])
                <p class="text-xs">Currently supported networks: {{ implode(', ', $supportedNetworkLabels) }}</p>
            @endif
            @if ($unsupportedCoins !== [])
                <p class="text-xs text-warning">Unsupported active catalog coins: {{ collect($unsupportedCoins)->pluck('symbol')->implode(', ') }}</p>
            @endif
        </div>
    @else
        <div>
            <label class="block text-sm font-medium mb-1">Coin</label>
            <div class="flex items-center gap-2">
                <template x-if="logo">
                    <img :src="logo" alt="" class="h-9 w-9 rounded-full bg-white shrink-0" width="36" height="36" referrerpolicy="no-referrer">
                </template>
                <div
                    x-show="! logo"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-border-default bg-elevated text-[10px] font-bold text-text-muted"
                    x-text="coin ? coin.slice(0, 3) : '—'"
                ></div>
                <select
                    name="coin"
                    x-model="coin"
                    @change="syncNetwork(true)"
                    required
                    class="min-w-0 flex-1 rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm"
                >
                    @foreach ($coinOptions as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <p class="mt-1 text-xs text-text-muted">From Coin Catalog (monitorable networks only). Max {{ $maxActive ?? 5 }} active wallets per coin + network.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Network</label>
            <select
                name="network"
                x-model="network"
                @change="syncConfs(true); showUsedBy = false"
                required
                class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm"
            >
                <template x-for="n in networks" :key="n.id">
                    <option :value="n.id" x-text="n.label"></option>
                </template>
            </select>
            <div class="mt-2 text-xs text-text-secondary" x-show="network" x-cloak>
                <button type="button" class="font-medium text-primary underline-offset-2 hover:underline" @click="showUsedBy = !showUsedBy">
                    Used by <span x-text="usedByRow.count"></span> coin<span x-text="usedByRow.count === 1 ? '' : 's'"></span>
                </button>
                <div class="mt-1 flex flex-wrap gap-1" x-show="showUsedBy" x-cloak>
                    <template x-for="c in usedByRow.coins" :key="c">
                        <span class="rounded-md bg-muted px-1.5 py-0.5 font-mono text-[11px]" x-text="c"></span>
                    </template>
                </div>
            </div>
        </div>
        <div class="md:col-span-2">
            <x-dashboard.input name="address" label="Deposit address" :value="old('address', $wallet->address ?? '')" required hint="The address customers send this coin to." />
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Required confirmations</label>
            <input
                type="number"
                name="required_confirmations"
                min="1"
                max="500"
                x-model.number="confs"
                class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm"
            >
            <p class="mt-1 text-xs text-text-muted">Defaults from Network Registry; override per wallet if needed.</p>
        </div>
        <input type="hidden" name="is_active" value="0">
        <x-dashboard.toggle name="is_active" label="Active (accept new orders)" :checked="old('is_active', $wallet->is_active ?? true)" value="1" />
        <div class="md:col-span-2 space-y-1">
            <input type="hidden" name="is_exchange_managed" value="0">
            <x-dashboard.toggle
                name="is_exchange_managed"
                label="Exchange-managed (auto-sweep)"
                :checked="old('is_exchange_managed', $wallet->is_exchange_managed ?? false)"
            />
            <p class="text-xs text-text-muted">Bybit-style deposit addresses that sweep to cold storage. Suppresses unexpected decrease alerts.</p>
        </div>
        <div>
            <x-dashboard.input name="label" label="Label (optional)" :value="old('label', $wallet->label ?? '')" hint="Short name shown in treasury / verify desk." />
        </div>
        <div>
            <x-dashboard.input name="purpose" label="Purpose (optional)" :value="old('purpose', $wallet->purpose ?? '')" />
        </div>
        <div class="md:col-span-2">
            <x-dashboard.input name="owner" label="Owner / custodian (optional)" :value="old('owner', $wallet->owner ?? '')" />
        </div>
    @endif
</div>
