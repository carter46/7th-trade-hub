@php
    use App\Support\Domains\DomainProductTldPolicy;

    $presetTlds = DomainProductTldPolicy::defaultFeaturedTlds();
    $allowedTlds = DomainProductTldPolicy::allowedTlds($product);
    $allowedSet = array_fill_keys($allowedTlds, true);
    $manualMode = (bool) ($manualPricingMode ?? false);
    /** @var \Illuminate\Support\Collection<string, \App\Models\DomainManualTldPrice> $manualPrices */
    $manualPrices = collect($manualTldPrices ?? []);
    $oldPrices = old('manual_tld_prices', []);
    $registryTlds = collect($registryTlds ?? [])
        ->pluck('tld')
        ->map(fn ($tld) => ltrim(strtolower((string) $tld), '.'))
        ->unique()
        ->values()
        ->all();
    $advancedPool = $manualMode
        ? array_values(array_unique(array_merge(
            DomainProductTldPolicy::normalizeList(config('domains.common_tlds', [])),
            $registryTlds,
            $manualPrices->keys()->all(),
            DomainProductTldPolicy::normalizeList(array_keys(is_array($oldPrices) ? $oldPrices : [])),
        )))
        : array_values(array_diff($registryTlds, $presetTlds));
    $advancedPool = array_values(array_diff($advancedPool, $presetTlds));

    $priceValue = function (string $tld) use ($oldPrices, $manualPrices): string {
        if (is_array($oldPrices) && array_key_exists($tld, $oldPrices) && $oldPrices[$tld] !== null && $oldPrices[$tld] !== '') {
            return (string) $oldPrices[$tld];
        }
        $row = $manualPrices->get($tld);

        return $row ? number_format((float) $row->retail_price, 2, '.', '') : '';
    };
@endphp

<div
    class="space-y-4 rounded-xl border border-border-subtle px-4 py-4"
    x-data="{
        advancedOpen: false,
        advancedQuery: '',
        matches(tld) {
            const q = (this.advancedQuery || '').trim().toLowerCase();
            if (!q) return true;
            return tld.includes(q) || ('.' + tld).includes(q);
        },
    }"
>
    <div>
        <p class="text-sm font-medium text-text-primary">Allowed extensions</p>
        <p class="mt-1 text-xs text-text-muted">
            Checked extensions appear in the customer search dropdown. Additional selections are available under advanced search only.
        </p>
        @if ($manualMode)
            <p class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                Manual pricing is used only while all domain providers are disabled. Enter a positive NGN price for each checked extension.
            </p>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        @foreach ($presetTlds as $tld)
            <label class="flex flex-col gap-2 rounded-lg border border-border-default px-2.5 py-2 text-sm sm:flex-row sm:items-center">
                <span class="flex min-w-0 items-center gap-2">
                    <input
                        type="checkbox"
                        name="allowed_tlds[]"
                        value="{{ $tld }}"
                        @checked(old('allowed_tlds') ? in_array($tld, old('allowed_tlds', []), true) : isset($allowedSet[$tld]))
                        class="accent-primary"
                    >
                    <span>.{{ $tld }}</span>
                </span>
                @if ($manualMode)
                    <input
                        type="number"
                        name="manual_tld_prices[{{ $tld }}]"
                        value="{{ $priceValue($tld) }}"
                        min="0.01"
                        step="0.01"
                        placeholder="NGN"
                        class="w-full rounded-md border border-border-default bg-elevated px-2 py-1.5 text-xs sm:max-w-[7.5rem]"
                        aria-label="Price for .{{ $tld }}"
                    >
                @endif
            </label>
        @endforeach
    </div>

    @error('allowed_tlds')
        <p class="text-xs text-danger">{{ $message }}</p>
    @enderror

    <div class="border-t border-border-default pt-3">
        <button
            type="button"
            class="text-sm font-medium text-primary hover:underline focus-ring"
            @click="advancedOpen = !advancedOpen"
        >
            <span x-text="advancedOpen ? 'Hide advanced extensions' : 'Advanced extension search'"></span>
        </button>

        <div x-show="advancedOpen" x-cloak class="mt-3 space-y-3">
            <input
                type="search"
                x-model="advancedQuery"
                placeholder="Search extensions (e.g. io, dev)..."
                class="w-full rounded-lg border-border-default bg-elevated text-sm"
            >
            <div class="max-h-64 overflow-y-auto rounded-xl border border-border-default p-3">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($advancedPool as $tld)
                        <label
                            class="flex flex-col gap-2 rounded-lg border border-border-default px-2.5 py-2 text-sm sm:flex-row sm:items-center"
                            x-show="matches('{{ $tld }}')"
                        >
                            <span class="flex min-w-0 items-center gap-2">
                                <input
                                    type="checkbox"
                                    name="allowed_tlds[]"
                                    value="{{ $tld }}"
                                    @checked(old('allowed_tlds') ? in_array($tld, old('allowed_tlds', []), true) : isset($allowedSet[$tld]))
                                    class="accent-primary"
                                >
                                <span>.{{ $tld }}</span>
                            </span>
                            @if ($manualMode)
                                <input
                                    type="number"
                                    name="manual_tld_prices[{{ $tld }}]"
                                    value="{{ $priceValue($tld) }}"
                                    min="0.01"
                                    step="0.01"
                                    placeholder="NGN"
                                    class="w-full rounded-md border border-border-default bg-elevated px-2 py-1.5 text-xs sm:max-w-[7.5rem]"
                                    aria-label="Price for .{{ $tld }}"
                                >
                            @endif
                        </label>
                    @endforeach
                </div>
                @if ($advancedPool === [])
                    <p class="text-sm text-text-muted">
                        @if ($manualMode)
                            No additional extensions yet. Checked preset extensions above are enough for manual pricing.
                        @else
                            No additional registry extensions loaded. Check domain provider connection.
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
