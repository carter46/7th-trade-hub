@php
    $rate = $rate ?? null;
    $coins = $coins ?? [];
    $selected = [
        'id' => old('coingecko_id', $rate?->coingecko_id),
        'symbol' => old('asset', $rate?->asset),
        'name' => old('asset', $rate?->asset),
        'logo' => old('logo_url', $rate?->logo_url),
        'price_ngn' => null,
    ];
    // OTC / public quotes use sell_rate_ngn as the payout when customers sell crypto to you.
    $initialCustomerRate = old('sell_rate_ngn', $rate?->sell_rate_ngn);
@endphp

{{--
  Platform only buys crypto from customers (customers sell to you).
  UI: market rate + your buy-from-customer rate.
  DB: sell_rate_ngn is the live OTC payout field; buy_rate_ngn is kept in sync for legacy columns.
--}}
<div
    class="space-y-4"
    x-data="{
        coins: @js($coins),
        selected: @js($selected),
        customerRate: @js($initialCustomerRate),
        marketRate: null,
        spreadPercent: 0,
        linkSpread: true,
        query: @js(($selected['symbol'] ?? null) ? (($selected['symbol'] ?? '').(isset($selected['name']) && $selected['name'] ? ' · '.$selected['name'] : '')) : ''),
        open: false,
        init() {
            if (this.selected?.price_ngn != null) {
                this.marketRate = this.roundRate(Number(this.selected.price_ngn));
            }
            this.syncSpreadFromRates();
            this.$watch('spreadPercent', () => { if (this.linkSpread) this.applyRateFromMarket(); });
            this.$watch('marketRate', () => { if (this.linkSpread) this.applyRateFromMarket(); });
            this.$watch('customerRate', () => {
                if (! this.linkSpread) this.syncSpreadFromRates();
            });
            this.$watch('linkSpread', (on) => {
                if (on) this.applyRateFromMarket();
            });
        },
        roundRate(n) {
            return Math.round(Number(n) * 100) / 100;
        },
        applyRateFromMarket() {
            const market = Number(this.marketRate);
            const pct = Number(this.spreadPercent);
            if (! (market > 0) || Number.isNaN(pct)) return;
            // Positive spread = you pay below market (your buy discount).
            this.customerRate = this.roundRate(market * (1 - pct / 100));
        },
        syncSpreadFromRates() {
            const market = Number(this.marketRate);
            const rate = Number(this.customerRate);
            if (! (market > 0) || ! (rate > 0)) {
                this.spreadPercent = 0;
                return;
            }
            this.spreadPercent = this.roundRate(((market - rate) / market) * 100);
        },
        get marketHint() {
            if (this.marketRate == null || Number.isNaN(Number(this.marketRate))) return '';
            return ' · Market ₦' + Number(this.marketRate).toLocaleString('en-NG');
        },
        get spreadDiff() {
            const market = Number(this.marketRate) || 0;
            const rate = Number(this.customerRate) || 0;
            return this.roundRate(market - rate);
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
        pick(coin) {
            this.selected = coin;
            this.query = `${coin.symbol} · ${coin.name}`;
            this.open = false;
            if (coin.price_ngn != null && Number(coin.price_ngn) > 0) {
                this.marketRate = this.roundRate(Number(coin.price_ngn));
                if (this.linkSpread) this.applyRateFromMarket();
                else if (! (Number(this.customerRate) > 0)) this.customerRate = this.marketRate;
            } else {
                this.marketRate = null;
            }
        },
    }"
>
    <div class="space-y-2">
        <label class="block text-sm font-medium text-text-primary">Coin <span class="text-danger">*</span></label>
        <p class="text-xs text-text-muted">You only buy crypto from customers. Set the NGN rate you pay them for this coin.</p>

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
                            <span class="block truncate text-[11px] text-text-muted" x-text="coin.price_ngn != null ? ('Market ₦' + Number(coin.price_ngn).toLocaleString('en-NG')) : coin.id"></span>
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
            <span x-show="marketHint" class="text-text-secondary" x-text="marketHint"></span>
        </p>
        @error('asset')
            <p class="text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-dashboard.input label="Processing time" name="processing_time" :value="old('processing_time', $rate?->processing_time)" placeholder="5–15 minutes" hint="Shown to customers on the exchange page." />
        <x-dashboard.input label="Min USD" name="min_amount_usd" type="number" step="any" :value="old('min_amount_usd', $rate?->min_amount_usd)" />
        <x-dashboard.input label="Max USD" name="max_amount_usd" type="number" step="any" :value="old('max_amount_usd', $rate?->max_amount_usd)" />

        <div class="sm:col-span-2 rounded-xl border border-border-subtle bg-muted/20 px-3 py-3 space-y-3">
            <div>
                <p class="text-sm font-medium text-text-primary">Current market rate (NGN)</p>
                <p class="mt-1 text-lg font-semibold text-text-primary" x-show="marketRate != null" x-cloak>
                    ₦<span x-text="Number(marketRate).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                    <span class="text-xs font-normal text-text-muted"> / USD (CoinGecko)</span>
                </p>
                <p class="mt-1 text-sm text-text-muted" x-show="marketRate == null" x-cloak>Pick a coin to load the live market rate.</p>
            </div>

            <label class="flex items-center gap-2 text-sm text-text-secondary">
                <input type="checkbox" class="rounded border-border-default" x-model="linkSpread" :disabled="marketRate == null">
                Set my buy rate from market with a spread %
            </label>
            <div class="max-w-xs">
                <label class="mb-1.5 block text-sm font-medium text-text-primary">Spread below market %</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    x-model.number="spreadPercent"
                    :disabled="! linkSpread || marketRate == null"
                    class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/40 disabled:opacity-60"
                >
                <p class="mt-1 text-[11px] text-text-muted">
                    Your buy rate = market × (1 − spread%). Example: market ₦1,500 with 2% → you pay ₦1,470.
                </p>
                <p class="mt-1 text-xs font-medium text-text-primary" x-show="linkSpread && marketRate != null" x-cloak>
                    You pay
                    <span x-text="Number(spreadDiff).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                    NGN less per $1 than market
                    <span class="font-normal text-text-muted" x-text="' (' + Number(spreadPercent || 0).toLocaleString('en-NG', { maximumFractionDigits: 2 }) + '%)'"></span>
                </p>
            </div>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-text-primary">Buy rate from customer (NGN)</label>
            {{-- Live OTC payout field (customers sell crypto to you). --}}
            <input
                type="number"
                step="0.01"
                name="sell_rate_ngn"
                x-model.number="customerRate"
                :readonly="linkSpread && marketRate != null"
                :class="(linkSpread && marketRate != null) ? 'opacity-80 cursor-not-allowed' : ''"
                class="w-full max-w-md rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/40"
            >
            {{-- Keep legacy buy column aligned; you do not sell crypto to customers. --}}
            <input type="hidden" name="buy_rate_ngn" :value="customerRate" value="{{ $initialCustomerRate }}">
            <p class="mt-1 text-[11px] text-text-muted">
                What you pay the customer in NGN when they sell you this coin (per $1 USD). This is the rate shown on the exchange / OTC sell flow.
                Live OTC Pricing can still override this when configured.
            </p>
        </div>
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
