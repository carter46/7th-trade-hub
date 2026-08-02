@php
    $rate = $rate ?? null;
    $coins = $coins ?? [];
    $usdNgnReference = (float) ($usdNgnReference ?? 0);
    $defaultSpread = (float) ($defaultSpread ?? 25);
    $coinSpread = (float) old('spread_ngn', $coinSpread ?? $defaultSpread);
    $calculatedBuyRate = $calculatedBuyRate ?? ($usdNgnReference > 0 ? max(0, $usdNgnReference - $coinSpread) : null);
    $initialCoinUsd = (float) ($initialCoinUsd ?? 0);
    $initialCoinNgn = $initialCoinNgn ?? null;
    $networkIdsByCoin = $networkIdsByCoin ?? [];
    $selectedNetworkIds = old('allowed_network_ids', $selectedNetworkIds ?? []);
    if (! is_array($selectedNetworkIds)) {
        $selectedNetworkIds = [];
    }
    $coinMarketUrl = $coinMarketUrl ?? route('admin.exchange-rates.coin-market');
    $otcSettingsUrl = $otcSettingsUrl ?? route('admin.otc-pricing');
    $selected = [
        'id' => old('coingecko_id', $rate?->coingecko_id),
        'symbol' => old('asset', $rate?->asset),
        'name' => old('asset', $rate?->asset),
        'logo' => old('logo_url', $rate?->logo_url),
    ];
@endphp

<div
    class="space-y-4"
    x-data="{
        coins: @js($coins),
        selected: @js($selected),
        networkIdsByCoin: @js($networkIdsByCoin),
        selectedNetworkIds: @js(array_values($selectedNetworkIds)),
        usdNgn: @js($usdNgnReference),
        coinSpread: @js($coinSpread),
        coinUsd: @js($initialCoinUsd > 0 ? $initialCoinUsd : null),
        coinNgn: @js($initialCoinNgn),
        marketLoading: false,
        get buyRate() {
            const m = Number(this.usdNgn);
            const s = Number(this.coinSpread);
            if (! (m > 0) || Number.isNaN(s)) return null;
            return Math.round(Math.max(0, m - s) * 100) / 100;
        },
        query: @js(($selected['symbol'] ?? null) ? (($selected['symbol'] ?? '').(isset($selected['name']) && $selected['name'] ? ' · '.$selected['name'] : '')) : ''),
        open: false,
        coinMarketUrl: @js($coinMarketUrl),
        init() {
            if (this.selected?.symbol) {
                this.refreshCoinMarket(this.selected.symbol, false);
                this.ensureNetworkSelection();
            }
            this.$watch('selected.symbol', () => this.ensureNetworkSelection());
        },
        get networkOptions() {
            const sym = (this.selected?.symbol || '').toUpperCase();
            return this.networkIdsByCoin[sym] || [];
        },
        ensureNetworkSelection() {
            const opts = this.networkOptions.map((o) => o.id);
            this.selectedNetworkIds = this.selectedNetworkIds.filter((id) => opts.includes(id));
            if (this.selectedNetworkIds.length === 0 && opts.length > 0) {
                this.selectedNetworkIds = [...opts];
            }
        },
        toggleNetwork(id) {
            if (this.selectedNetworkIds.includes(id)) {
                this.selectedNetworkIds = this.selectedNetworkIds.filter((x) => x !== id);
            } else {
                this.selectedNetworkIds = [...this.selectedNetworkIds, id];
            }
        },
        filteredCoins() {
            const q = (this.query || '').trim().toLowerCase();
            const list = this.coins || [];
            if (! q) return list.slice(0, 40);
            return list.filter((c) => {
                const hay = `${c.symbol || ''} ${c.name || ''} ${c.id || ''}`.toLowerCase();
                return hay.includes(q);
            }).slice(0, 40);
        },
        async refreshCoinMarket(symbol, resetNetworks = true) {
            if (! symbol) return;
            this.marketLoading = true;
            try {
                const res = await fetch(this.coinMarketUrl + '?asset=' + encodeURIComponent(symbol), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json().catch(() => ({}));
                if (data.ok) {
                    this.coinUsd = data.coin_usd > 0 ? data.coin_usd : null;
                    this.coinNgn = data.coin_ngn;
                    if (data.usd_ngn > 0) this.usdNgn = data.usd_ngn;
                }
            } catch (e) {
                // keep previous
            } finally {
                this.marketLoading = false;
                if (resetNetworks) this.ensureNetworkSelection();
            }
        },
        pick(coin) {
            this.selected = coin;
            this.query = `${coin.symbol} · ${coin.name}`;
            this.open = false;
            this.selectedNetworkIds = [];
            this.refreshCoinMarket(coin.symbol, true);
        },
    }"
>
    <div class="space-y-2">
        <label class="block text-sm font-medium text-text-primary">Coin <span class="text-danger">*</span></label>
        <p class="text-xs text-text-muted">Add coins, per-coin spread, and deposit networks. Market USD→NGN is set under OTC Pricing.</p>

        <div class="relative">
            <div class="flex items-center gap-2 rounded-xl border border-border-default bg-elevated px-3 py-2">
                <template x-if="selected?.logo">
                    <img :src="selected.logo" alt="" class="h-7 w-7 rounded-full bg-white shrink-0" width="28" height="28" referrerpolicy="no-referrer">
                </template>
                <input
                    type="search"
                    x-model="query"
                    @focus="open = true"
                    @input="open = true"
                    placeholder="Search Bitcoin, USDT, Solana…"
                    class="min-w-0 flex-1 border-0 bg-transparent text-sm text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-0"
                    autocomplete="off"
                >
                <button type="button" class="text-xs text-primary shrink-0" @click="open = !open" x-text="open ? 'Close' : 'Browse'"></button>
            </div>

            <div
                x-show="open"
                x-cloak
                @click.outside="open = false"
                class="absolute z-30 mt-1 max-h-72 w-full overflow-y-auto rounded-xl border border-border-default bg-elevated shadow-panel"
            >
                <template x-if="filteredCoins().length === 0">
                    <p class="px-3 py-4 text-sm text-text-muted">No coins match.</p>
                </template>
                <template x-for="coin in filteredCoins()" :key="coin.id">
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 px-3 py-2.5 text-left hover:bg-muted/60"
                        @click="pick(coin)"
                    >
                        <img x-show="coin.logo" :src="coin.logo" alt="" class="h-7 w-7 rounded-full bg-white shrink-0" width="28" height="28" referrerpolicy="no-referrer">
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-text-primary" x-text="coin.symbol + ' · ' + coin.name"></span>
                            <span class="block truncate text-[11px] text-text-muted" x-text="coin.id"></span>
                        </span>
                    </button>
                </template>
            </div>
        </div>

        <input type="hidden" name="asset" :value="selected?.symbol || ''" value="{{ old('asset', $rate?->asset) }}">
        <input type="hidden" name="coingecko_id" :value="selected?.id || ''" value="{{ old('coingecko_id', $rate?->coingecko_id) }}">
        <input type="hidden" name="logo_url" :value="selected?.logo || ''" value="{{ old('logo_url', $rate?->logo_url) }}">

        <p class="text-xs text-text-muted" x-show="selected?.symbol">
            Selected: <span class="font-medium text-text-primary" x-text="(selected?.symbol || '') + (selected?.name ? (' — ' + selected.name) : '')"></span>
        </p>
        @error('asset')
            <p class="text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-dashboard.input label="Processing time" name="processing_time" :value="old('processing_time', $rate?->processing_time)" placeholder="5–15 minutes" hint="Shown to customers on the exchange page." />
        <x-dashboard.input label="Min USD" name="min_amount_usd" type="number" step="any" :value="old('min_amount_usd', $rate?->min_amount_usd)" />
        <x-dashboard.input label="Max USD" name="max_amount_usd" type="number" step="any" :value="old('max_amount_usd', $rate?->max_amount_usd)" />
    </div>

    {{-- 1. Current coin market (Bybit) --}}
    <div class="rounded-xl border border-border-subtle bg-muted/20 px-4 py-3 space-y-1">
        <p class="text-sm font-medium text-text-primary">
            Current <span x-text="selected?.symbol || 'coin'"></span> Market
        </p>
        <p class="text-xs text-text-muted" x-show="marketLoading" x-cloak>Loading Bybit price…</p>
        <template x-if="!marketLoading && coinUsd">
            <div>
                <p class="text-lg font-semibold text-text-primary">
                    ≈ $<span x-text="Number(coinUsd).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 })"></span>
                </p>
                <p class="text-sm text-text-secondary" x-show="coinNgn">
                    ≈ ₦<span x-text="Number(coinNgn).toLocaleString('en-NG', { maximumFractionDigits: 0 })"></span>
                    per <span x-text="selected?.symbol"></span>
                </p>
                <p class="mt-1 text-[11px] uppercase tracking-wide text-text-muted">Source: Bybit Spot</p>
            </div>
        </template>
        <p class="text-sm text-text-muted" x-show="!marketLoading && !coinUsd" x-cloak>
            Pick a coin to load the live coin price (not the ₦/$1 buy rate).
        </p>
    </div>

    {{-- 2. Pricing: global market + this coin's spread --}}
    <div class="rounded-xl border border-border-default bg-elevated px-4 py-4 space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <p class="text-sm font-medium text-text-primary">Our Buy Rate</p>
                <p class="text-xs text-text-muted">Market (OTC) − this coin’s spread. Not typed by hand.</p>
            </div>
            <a href="{{ $otcSettingsUrl }}" class="text-sm font-medium text-primary underline-offset-2 hover:underline">Configure market in OTC Pricing</a>
        </div>

        @if ($usdNgnReference > 0)
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-text-muted">Market USD→NGN</p>
                    <p class="text-lg font-semibold text-text-primary">
                        ₦{{ number_format($usdNgnReference, 2) }}
                        <span class="text-sm font-normal text-text-muted">/ $1</span>
                    </p>
                    <p class="text-[11px] text-text-muted">Inherited from OTC Pricing</p>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] uppercase tracking-wide text-text-muted">Our Spread (this coin)</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-text-muted">₦</span>
                        <input
                            type="number"
                            name="spread_ngn"
                            step="0.01"
                            min="0"
                            max="1000"
                            required
                            x-model.number="coinSpread"
                            class="w-full rounded-xl border border-border-default bg-elevated py-2.5 pl-8 pr-3 text-sm"
                        >
                    </div>
                    @error('spread_ngn')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-text-muted">You pay (calculated)</p>
                    <p class="text-lg font-semibold text-text-primary" x-show="buyRate !== null" x-cloak>
                        ₦<span x-text="Number(buyRate).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                        <span class="text-sm font-normal text-text-muted">/ $1</span>
                    </p>
                    <p class="text-[11px] text-text-muted">Market − spread</p>
                </div>
            </div>
        @else
            <input type="hidden" name="spread_ngn" value="{{ $coinSpread }}">
            <p class="text-sm text-warning">
                Set Market USD→NGN in
                <a href="{{ $otcSettingsUrl }}" class="font-medium underline-offset-2 hover:underline">OTC Pricing</a>
                before taking sell orders.
            </p>
        @endif
    </div>

    {{-- Deposit networks (whitelist only) --}}
    <div class="rounded-xl border border-border-subtle px-4 py-4 space-y-3">
        <div>
            <p class="text-sm font-medium text-text-primary">Deposit networks</p>
            <p class="text-xs text-text-muted">Only networks this coin can use. Stored as canonical IDs for matching.</p>
        </div>

        <template x-if="networkOptions.length === 0">
            <p class="text-sm text-text-muted">
                No deposit networks configured for this asset. It will not appear when creating deposit wallets until you add networks.
            </p>
        </template>

        <div class="space-y-2" x-show="networkOptions.length > 0" x-cloak>
            <template x-for="opt in networkOptions" :key="opt.id">
                <label class="flex items-center gap-2 text-sm text-text-primary">
                    <input
                        type="checkbox"
                        class="rounded border-border-default"
                        :value="opt.id"
                        :checked="selectedNetworkIds.includes(opt.id)"
                        @change="toggleNetwork(opt.id)"
                    >
                    <span x-text="opt.label"></span>
                </label>
            </template>
            <template x-for="id in selectedNetworkIds" :key="'hid-'+id">
                <input type="hidden" name="allowed_network_ids[]" :value="id">
            </template>
        </div>
        @error('allowed_network_ids')
            <p class="text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <label class="flex items-center gap-2 text-sm text-text-secondary">
        <input type="checkbox" name="is_featured" value="1" class="rounded border-border-default" @checked(old('is_featured', $rate?->is_featured))>
        Featured
    </label>
    <label class="flex items-center gap-2 text-sm text-text-secondary">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="rounded border-border-default" @checked(old('is_active', $rate?->is_active ?? true))>
        Active
    </label>
</div>
