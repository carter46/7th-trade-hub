@php
    $quoteUrl = route('dashboard.services.domain-quote');
    $checkoutBase = route('dashboard.services.checkout', $product->slug);
@endphp
<x-dashboard.card class="space-y-4 h-fit">
    <div>
        <p class="text-sm font-medium text-text-primary">Find your domain</p>
        <p class="mt-1 text-xs text-text-muted">Search availability and get a live price before checkout.</p>
    </div>

    <div
        class="space-y-4"
        x-data="domainProductSearch(@js([
            'productSlug' => $product->slug,
            'quoteUrl' => $quoteUrl,
            'checkoutBase' => $checkoutBase,
            'domainTlds' => $domainTlds ?? [],
            'csrfToken' => csrf_token(),
        ]))"
    >
        <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
            <div>
                <label class="mb-1 block text-xs text-text-muted">Domain name</label>
                <input
                    type="text"
                    x-model="domainLabel"
                    @input="invalidateQuote()"
                    placeholder="mysite"
                    autocomplete="off"
                    class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
                >
            </div>
            <div class="sm:w-36">
                <label class="mb-1 block text-xs text-text-muted">Extension</label>
                <select
                    x-model="domainTld"
                    @change="invalidateQuote()"
                    class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
                >
                    <template x-for="row in domainTlds" :key="row.tld">
                        <option :value="row.tld" x-text="row.label"></option>
                    </template>
                </select>
            </div>
        </div>

        <x-dashboard.button
            type="button"
            variant="secondary"
            class="w-full sm:w-auto"
            x-on:click="checkDomain()"
            x-bind:disabled="domainChecking || !domainLabel.trim()"
        >
            <span x-show="!domainChecking">Check availability</span>
            <span x-cloak x-show="domainChecking">Checking…</span>
        </x-dashboard.button>

        <div x-show="domainMessage" x-cloak class="rounded-xl border border-border-default bg-muted/30 px-4 py-3 text-sm">
            <p x-text="domainMessage" :class="domainAvailable ? 'text-emerald-700' : 'text-text-secondary'"></p>
            <p x-show="domainAvailable" class="mt-2 text-lg font-semibold text-primary">
                <span x-text="'₦' + retailFormatted"></span>
                <span x-show="domainPremium" class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Premium</span>
            </p>
        </div>

        <x-dashboard.button
            type="button"
            variant="primary"
            icon="orders"
            class="w-full"
            x-on:click="goCheckout()"
            x-bind:disabled="!canCheckout"
        >Continue to checkout</x-dashboard.button>
    </div>
</x-dashboard.card>
