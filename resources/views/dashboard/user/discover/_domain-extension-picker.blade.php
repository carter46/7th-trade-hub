<div class="space-y-2">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <label class="block text-xs text-text-muted">Extension</label>
        <button
            type="button"
            class="text-xs font-medium text-primary hover:underline focus-ring"
            @click="showAdvancedTlds = !showAdvancedTlds"
            x-show="domainTldsAdvanced.length > 0"
        >
            <span x-text="showAdvancedTlds ? 'Hide advanced' : 'More extensions'"></span>
        </button>
    </div>

    <div x-show="isAdvancedTldSelected" x-cloak class="flex flex-wrap items-center gap-2 text-xs">
        <p class="font-medium text-primary" x-text="'Selected: .' + domainTld"></p>
        <button
            type="button"
            class="font-medium text-text-muted hover:text-primary hover:underline focus-ring"
            @click="resetToFeaturedTld()"
        >Use common extension</button>
    </div>

    <select
        x-model="domainTld"
        @change="invalidateQuote()"
        x-show="!isAdvancedTldSelected"
        class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
    >
        <template x-for="row in domainTlds" :key="row.tld">
            <option :value="row.tld" x-text="row.label"></option>
        </template>
    </select>

    <div x-show="showAdvancedTlds" x-cloak class="space-y-2 rounded-xl border border-border-default bg-muted/20 p-3">
        <input
            type="search"
            x-model="advancedQuery"
            placeholder="Search extensions..."
            class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
        >
        <div class="max-h-40 overflow-y-auto">
            <div class="flex flex-wrap gap-2">
                <template x-for="row in filteredAdvancedTlds" :key="'adv-' + row.tld">
                    <button
                        type="button"
                        class="rounded-lg border border-border-default bg-elevated px-2.5 py-1 text-xs font-medium hover:border-primary hover:text-primary"
                        :class="domainTld === row.tld ? 'border-primary text-primary' : 'text-text-secondary'"
                        @click="pickAdvancedTld(row.tld)"
                        x-text="row.label"
                    ></button>
                </template>
            </div>
            <p x-show="filteredAdvancedTlds.length === 0" class="text-xs text-text-muted">No matching extensions.</p>
        </div>
    </div>
</div>
