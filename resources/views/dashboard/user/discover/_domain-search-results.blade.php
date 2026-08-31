<div x-show="domainMessage || domainAvailable" x-cloak class="rounded-xl border border-border-default bg-muted/30 px-4 py-3 text-sm space-y-3">
    <p x-show="domainMessage" x-text="domainMessage" :class="domainAvailable ? 'text-emerald-700' : 'text-text-secondary'"></p>
    <p x-show="domainAvailable" class="text-lg font-semibold text-primary break-all">
        <span x-text="domainFqdn"></span>
        <span class="mx-1">·</span>
        <span class="whitespace-nowrap" x-text="'₦' + retailFormatted"></span>
        <span x-show="domainPremium" class="ml-2 inline-block whitespace-nowrap rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Premium</span>
    </p>

    <div x-show="domainSuggestions.length > 0" class="space-y-2 border-t border-border-default pt-3">
        <p class="text-xs font-medium text-text-secondary">Available extensions</p>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
            <template x-for="row in domainSuggestions" :key="'suggest-' + row.tld">
                <button
                    type="button"
                    class="min-w-0 w-full overflow-hidden rounded-xl border px-3 py-2 text-left text-sm transition-colors"
                    :class="selectedSuggestionTld === row.tld ? 'border-primary bg-primary/5 text-primary ring-1 ring-primary/30' : 'border-border-default bg-elevated hover:border-primary/40'"
                    @click="selectSuggestion(row)"
                >
                    <span class="flex items-start justify-between gap-2">
                        <span class="min-w-0 flex-1 break-all font-medium leading-snug" x-text="row.fqdn"></span>
                        <span
                            x-show="selectedSuggestionTld === row.tld"
                            class="mt-0.5 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-primary"
                        >Selected</span>
                    </span>
                    <span class="mt-0.5 block text-xs" :class="selectedSuggestionTld === row.tld ? 'text-primary' : 'text-text-muted'">
                        <span x-text="'₦' + formatSuggestionPrice(row.retail_price)"></span>
                        <span x-show="row.premium" class="ml-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-800">Premium</span>
                    </span>
                </button>
            </template>
        </div>
        <p class="text-xs text-text-muted">Tap an extension to select it — others stay available so you can switch.</p>
    </div>
</div>
