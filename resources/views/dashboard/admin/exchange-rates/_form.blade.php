@php
    $rate = $rate ?? null;
    $coins = $coins ?? [];
    $usdNgnReference = (float) ($usdNgnReference ?? 0);
    $initialCoinUsd = (float) ($initialCoinUsd ?? 0);
    $initialCoinNgn = $initialCoinNgn ?? null;
    $initialBuyRate = old('sell_rate_ngn', $initialBuyRate ?? null);
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
        coinUsd: @js($initialCoinUsd > 0 ? $initialCoinUsd : null),
        coinNgn: @js($initialCoinNgn),
        customerRate: @js($initialBuyRate !== null ? (float) $initialBuyRate : null),
        spreadNgn: 25,
        linkSpread: true,
        marketLoading: false,
        query: @js(($selected['symbol'] ?? null) ? (($selected['symbol'] ?? '').(isset($selected['name']) && $selected['name'] ? ' · '.$selected['name'] : '')) : ''),
        open: false,
        coinMarketUrl: @js($coinMarketUrl),
        init() {
            this.syncSpreadFromRates();
            if (this.selected?.symbol) {
                this.refreshCoinMarket(this.selected.symbol, false);
                this.ensureNetworkSelection();
            }
            this.$watch('spreadNgn', () => { if (this.linkSpread) this.applyRateFromFx(); });
            this.$watch('usdNgn', () => { if (this.linkSpread) this.applyRateFromFx(); });
            this.$watch('customerRate', () => {
                if (! this.linkSpread) this.syncSpreadFromRates();
            });
            this.$watch('linkSpread', (on) => {
                if (on) this.applyRateFromFx();
            });
            this.$watch('selected.symbol', () => this.ensureNetworkSelection());
        },
        roundRate(n) {
            return Math.round(Number(n) * 100) / 100;
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
        applyRateFromFx() {
            const market = Number(this.usdNgn);
            const spread = Number(this.spreadNgn);
            if (! (market > 0) || Number.isNaN(spread)) return;
            this.customerRate = this.roundRate(Math.max(0, market - spread));
        },
        syncSpreadFromRates() {
            const market = Number(this.usdNgn);
            const rate = Number(this.customerRate);
            if (! (market > 0) || ! (rate > 0)) {
                return;
            }
            this.spreadNgn = this.roundRate(Math.max(0, market - rate));
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
                    if (this.linkSpread) this.applyRateFromFx();
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
        <p class="text-xs text-text-muted">You buy crypto from customers. Set Our Buy Rate as ₦ per $1 for this coin.</p>

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

    {{-- 2. OTC USD→NGN --}}
    <div class="rounded-xl border border-border-subtle bg-muted/20 px-4 py-3 space-y-1">
        <p class="text-sm font-medium text-text-primary">Market USD→NGN</p>
        <p class="text-lg font-semibold text-text-primary" x-show="usdNgn > 0" x-cloak>
            ₦<span x-text="Number(usdNgn).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
            <span class="text-sm font-normal text-text-muted">/ $1</span>
        </p>
        <p class="text-sm text-text-muted" x-show="!(usdNgn > 0)" x-cloak>Set the OTC market reference in OTC Settings.</p>
        <p class="text-[11px] text-text-muted">
            Source: OTC Market Reference ·
            <a href="{{ $otcSettingsUrl }}" class="text-primary underline-offset-2 hover:underline">OTC Settings</a>
        </p>
    </div>

    {{-- 3. Our Buy Rate --}}
    <div class="rounded-xl border border-border-default bg-elevated px-4 py-4 space-y-3">
        <div>
            <p class="text-sm font-medium text-text-primary">Our Buy Rate</p>
            <p class="text-xs text-text-muted">What you pay the customer per $1 USD of this coin.</p>
        </div>

        <label class="flex items-center gap-2 text-sm text-text-secondary">
            <input type="checkbox" class="rounded border-border-default" x-model="linkSpread" :disabled="!(usdNgn > 0)">
            Derive buy rate from OTC FX − spread ₦
        </label>

        <div class="max-w-xs" x-show="linkSpread" x-cloak>
            <label class="mb-1.5 block text-sm font-medium text-text-primary">Our Spread (₦ below market)</label>
            <input
                type="number"
                step="0.01"
                min="0"
                x-model.number="spreadNgn"
                class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm"
            >
            <p class="mt-1 text-[11px] text-text-muted">
                Buy rate = USD→NGN − spread. Example: ₦1,420 − ₦25 → you pay ₦1,395 / $1.
            </p>
        </div>

        <div class="max-w-md">
            <label class="mb-1.5 block text-sm font-medium text-text-primary">Buy rate (₦ per $1) <span class="text-danger">*</span></label>
            <input
                type="number"
                step="0.01"
                min="0.01"
                max="{{ \App\Models\ExchangeRate::maxBuyRatePerUsd() }}"
                name="sell_rate_ngn"
                required
                x-model.number="customerRate"
                :readonly="linkSpread && usdNgn > 0"
                :class="(linkSpread && usdNgn > 0) ? 'opacity-80 cursor-not-allowed' : ''"
                class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/40"
            >
            <input type="hidden" name="buy_rate_ngn" :value="customerRate" value="{{ $initialBuyRate }}">
            @error('sell_rate_ngn')
                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Deposit networks (whitelist only) --}}
    <div class="rounded-xl border border-border-subtle px-4 py-4 space-y-3">
        <div>
            <p class="text-sm font-medium text-text-primary">Deposit networks</p>
            <p class="text-xs text-text-muted">Only networks this coin can use. Stored as canonical IDs for matching.</p>
        </div>

        <template x-if="networkOptions.length === 0">
            <p class="text-sm text-text-muted">
                No deposit networks configured for this asset. It can still have a buy rate, but it will not appear when creating deposit wallets.
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
