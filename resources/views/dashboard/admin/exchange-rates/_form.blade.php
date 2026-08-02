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
@endphp

<div
    class="space-y-4"
    x-data="exchangeRateForm({
        coins: @js($coins),
        selected: @js($selected),
        buy: @js(old('buy_rate_ngn', $rate?->buy_rate_ngn)),
        sell: @js(old('sell_rate_ngn', $rate?->sell_rate_ngn)),
    })"
>
    <div class="space-y-2">
        <label class="block text-sm font-medium text-text-primary">Coin <span class="text-danger">*</span></label>
        <p class="text-xs text-text-muted">Pick from CoinGecko. OTC NGN rates are set under Admin → OTC Pricing.</p>

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
        <x-dashboard.input label="Bybit spot symbol" name="bybit_symbol" :value="old('bybit_symbol', $rate?->bybit_symbol)" placeholder="BTCUSDT" hint="Optional override for USD price lookup." />
        <x-dashboard.input label="Processing time" name="processing_time" :value="old('processing_time', $rate?->processing_time)" placeholder="5–15 minutes" />
        <x-dashboard.input label="Min USD" name="min_amount_usd" type="number" step="any" :value="old('min_amount_usd', $rate?->min_amount_usd)" />
        <x-dashboard.input label="Max USD" name="max_amount_usd" type="number" step="any" :value="old('max_amount_usd', $rate?->max_amount_usd)" />
        <div>
            <label class="mb-1.5 block text-sm font-medium text-text-primary">Legacy buy rate (NGN)</label>
            <input type="number" step="0.01" name="buy_rate_ngn" x-model="buy" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/40">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-text-primary">Legacy sell rate (NGN)</label>
            <input type="number" step="0.01" name="sell_rate_ngn" x-model="sell" class="w-full rounded-xl border border-border-default bg-elevated px-3 py-2.5 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/40">
            <p class="mt-1 text-[11px] text-text-muted">Prefer OTC Pricing for live customer rates.</p>
        </div>
    </div>

    <x-dashboard.input label="Sort order" name="sort_order" type="number" min="0" :value="old('sort_order', $rate?->sort_order ?? 0)" />
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
