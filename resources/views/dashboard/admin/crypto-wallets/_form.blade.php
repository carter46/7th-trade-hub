@php
    $wallet = $wallet ?? null;
    $networksByCoin = $networksByCoin ?? config('crypto.networks_by_coin', []);
    $coinOptions = array_keys($networksByCoin);
    $selectedCoin = old('coin', $wallet->coin ?? ($coinOptions[0] ?? 'BTC'));
    $selectedNetwork = old('network', $wallet->network ?? '');
@endphp
<div
    class="grid grid-cols-1 md:grid-cols-2 gap-4"
    x-data="{
        coin: @js($selectedCoin),
        network: @js($selectedNetwork),
        map: @js($networksByCoin),
        get networks() { return this.map[this.coin] || []; },
        syncNetwork() {
            if (!this.networks.includes(this.network)) {
                this.network = this.networks[0] || '';
            }
        }
    }"
    x-init="syncNetwork()"
>
    <div>
        <label class="block text-sm font-medium mb-1">Coin</label>
        <select name="coin" x-model="coin" @change="syncNetwork()" required class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
            @foreach ($coinOptions as $c)
                <option value="{{ $c }}">{{ $c }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-text-muted">Max {{ $maxActive ?? 5 }} active wallets per coin + network.</p>
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
        <x-dashboard.input name="address" label="Deposit address" :value="old('address', $wallet->address ?? '')" required />
    </div>
    <x-dashboard.input name="required_confirmations" type="number" label="Required confirmations" :value="old('required_confirmations', $wallet->required_confirmations ?? 2)" required />
    <x-dashboard.input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $wallet->sort_order ?? 0)" />
    <div>
        <label class="block text-sm font-medium mb-1">Purpose</label>
        <select name="purpose" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm">
            @foreach (['Hot','Cold','Ledger','Binance','Trust Wallet','Office','Other'] as $p)
                <option value="{{ $p }}" @selected(old('purpose', $wallet->purpose ?? '') === $p)>{{ $p }}</option>
            @endforeach
        </select>
    </div>
    <x-dashboard.input name="owner" label="Owner" :value="old('owner', $wallet->owner ?? '')" />
    <x-dashboard.input name="label" label="Label" :value="old('label', $wallet->label ?? '')" />
    <x-dashboard.input name="estimated_holdings" type="number" step="any" label="Estimated holdings" :value="old('estimated_holdings', $wallet->estimated_holdings ?? '')" />
    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2 text-sm">{{ old('notes', $wallet->notes ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Customer instructions</label>
        <textarea name="instructions" rows="2" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2 text-sm">{{ old('instructions', $wallet->instructions ?? '') }}</textarea>
    </div>
    <input type="hidden" name="is_active" value="0">
    <x-dashboard.toggle name="is_active" label="Active (accept new orders)" :checked="old('is_active', $wallet->is_active ?? true)" value="1" />
</div>
