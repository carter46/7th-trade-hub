<div x-show="domainMessage || domainAvailable" x-cloak class="rounded-xl border border-border-default bg-muted/30 px-4 py-3 text-sm space-y-3">
    <p x-show="domainMessage" x-text="domainMessage" :class="domainAvailable ? 'text-emerald-700' : 'text-text-secondary'"></p>
    <p x-show="domainAvailable" class="text-lg font-semibold text-primary">
        <span x-text="domainFqdn"></span>
        <span class="mx-1">·</span>
        <span x-text="'₦' + retailFormatted"></span>
        <span x-show="domainPremium" class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Premium</span>
    </p>

    <div x-show="domainSuggestions.length > 0" class="space-y-2 border-t border-border-default pt-3">
        <p class="text-xs font-medium text-text-secondary">Other available extensions</p>
        <div class="grid gap-2 sm:grid-cols-3">
            <template x-for="row in domainSuggestions" :key="'suggest-' + row.tld">
                <button
                    type="button"
                    class="rounded-xl border px-3 py-2 text-left text-sm transition-colors"
                    :class="selectedSuggestionTld === row.tld ? 'border-primary bg-primary/5 text-primary' : 'border-border-default bg-elevated hover:border-primary/40'"
                    @click="selectSuggestion(row)"
                >
                    <span class="block font-medium" x-text="row.fqdn"></span>
                    <span class="mt-0.5 block text-xs" :class="selectedSuggestionTld === row.tld ? 'text-primary' : 'text-text-muted'">
                        <span x-text="'₦' + formatSuggestionPrice(row.retail_price)"></span>
                        <span x-show="row.premium" class="ml-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-800">Premium</span>
                    </span>
                </button>
            </template>
        </div>
        <p class="text-xs text-text-muted">Select an extension above, then continue to checkout.</p>
    </div>
</div>
