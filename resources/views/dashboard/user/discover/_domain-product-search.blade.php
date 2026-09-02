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
            'domainTldsAdvanced' => $domainTldsAdvanced ?? [],
            'csrfToken' => csrf_token(),
        ]))"
    >
        <div class="grid gap-3 sm:grid-cols-[1fr_minmax(9rem,12rem)]">
            <div>
                <label class="mb-1 block text-xs text-text-muted">Domain name</label>
                <input
                    type="text"
                    x-model="domainLabel"
                    @input="onDomainLabelInput($event)"
                    placeholder="mysite"
                    autocomplete="off"
                    autocapitalize="off"
                    spellcheck="false"
                    :class="domainLabelError ? 'border-danger' : 'border-border-default'"
                    class="w-full rounded-lg bg-elevated text-text-primary text-sm"
                >
                <p x-show="domainLabelError" x-cloak class="mt-1 text-xs text-danger" x-text="domainLabelError"></p>
            </div>
            <div>
                @include('dashboard.user.discover._domain-extension-picker')
            </div>
        </div>

        <x-dashboard.button
            type="button"
            variant="primary"
            class="w-full sm:w-auto"
            x-on:click="checkDomain()"
            x-bind:disabled="domainChecking || !canCheckDomain"
        >
            <span x-text="domainChecking ? 'Checking…' : 'Check availability'">Check availability</span>
        </x-dashboard.button>

        @include('dashboard.user.discover._domain-search-results')

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
