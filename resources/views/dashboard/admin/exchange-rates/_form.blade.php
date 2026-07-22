@php $rate = $rate ?? null; @endphp
<x-dashboard.input label="Asset" name="asset" :value="old('asset', $rate?->asset)" placeholder="BTC" required />
<x-dashboard.input label="Buy rate (NGN)" name="buy_rate_ngn" type="number" step="0.01" :value="old('buy_rate_ngn', $rate?->buy_rate_ngn)" required />
<x-dashboard.input label="Sell rate (NGN)" name="sell_rate_ngn" type="number" step="0.01" :value="old('sell_rate_ngn', $rate?->sell_rate_ngn)" required />
<x-dashboard.input label="Minimum amount" name="minimum_amount" type="number" step="any" :value="old('minimum_amount', $rate?->minimum_amount)" />
<x-dashboard.input label="Maximum amount" name="maximum_amount" type="number" step="any" :value="old('maximum_amount', $rate?->maximum_amount)" />
<x-dashboard.input label="Processing time" name="processing_time" :value="old('processing_time', $rate?->processing_time)" placeholder="5–15 minutes" />
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
