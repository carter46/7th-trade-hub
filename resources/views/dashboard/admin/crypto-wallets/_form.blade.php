@php
    $wallet = $wallet ?? null;
    $catalogCoins = $catalogCoins ?? [];
    $networksByCoin = $networksByCoin ?? [];
    $coinOptions = array_values(array_keys($networksByCoin));
    $selectedCoin = old('coin', $wallet->coin ?? ($coinOptions[0] ?? ''));
    $selectedNetwork = old('network', $wallet->network ?? '');
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
        get networks() { return this.map[this.coin] || []; },
        get logo() { return this.logos[this.coin] || null; },
        syncNetwork() {
            if (! this.networks.includes(this.network)) {
                this.network = this.networks[0] || '';
            }
        }
    }"
    x-init="syncNetwork()"
>
    @if ($coinOptions === [])
        <div class="md:col-span-2 rounded-xl border border-warning/40 bg-warning/10 px-3 py-3 text-sm text-text-secondary">
            No active coins in
            <a href="{{ route('admin.exchange-rates') }}" class="font-medium text-primary underline-offset-2 hover:underline">Coin Catalog</a>.
            Add a coin there first, then create a deposit wallet.
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
                    @change="syncNetwork()"
                    required
                    class="min-w-0 flex-1 rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm"
                >
                    @foreach ($coinOptions as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <p class="mt-1 text-xs text-text-muted">From Coin Catalog. Max {{ $maxActive ?? 5 }} active wallets per coin + network.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Network</label>
            <select name="network" x-model="network" required class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
                <template x-for="n in networks" :key="n">
                    <option :value="n" x-text="n"></option>
                </template>
            </select>
        </div>
        <div class="md:col-span-2">
            <x-dashboard.input name="address" label="Deposit address" :value="old('address', $wallet->address ?? '')" required hint="The address customers send this coin to." />
        </div>
        <div class="md:col-span-2">
            <p class="text-xs text-text-muted">
                Confirmations are applied automatically for the selected network (how many blocks before a deposit is ready). You do not need to set this.
            </p>
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
    @endif
</div>
