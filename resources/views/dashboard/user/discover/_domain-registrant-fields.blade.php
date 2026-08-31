<div class="space-y-3 rounded-xl border border-border-default bg-muted/20 px-4 py-4">
    @php
        $registrantErrors = collect($errors->getMessages())->filter(fn ($messages, $key) => str_starts_with((string) $key, 'registrant.'));
    @endphp
    @if($registrantErrors->isNotEmpty())
        <x-dashboard.alert type="danger">Please correct the registrant contact details below.</x-dashboard.alert>
    @endif

    <div>
        <p class="text-sm font-semibold text-text-primary">Registrant contact</p>
        <p class="mt-1 text-xs text-text-muted">Required for domain registration (WHOIS). Use the legal owner&apos;s details.</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs text-text-muted">First name <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[first_name]"
                x-model="registrant.first_name"
                value="{{ old('registrant.first_name') }}"
                autocomplete="given-name"
                class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
            >
        </div>
        <div>
            <label class="mb-1 block text-xs text-text-muted">Last name <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[last_name]"
                x-model="registrant.last_name"
                value="{{ old('registrant.last_name') }}"
                autocomplete="family-name"
                class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
            >
        </div>
    </div>

    <div>
        <label class="mb-1 block text-xs text-text-muted">Company / organization <span class="text-text-muted">(optional)</span></label>
        <input
            type="text"
            name="registrant[company]"
            x-model="registrant.company"
            value="{{ old('registrant.company') }}"
            autocomplete="organization"
            class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
        >
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs text-text-muted">Email <span class="text-danger">*</span></label>
            <input
                type="email"
                name="registrant[email]"
                x-model="registrant.email"
                value="{{ old('registrant.email', auth()->user()->email) }}"
                autocomplete="email"
                class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
            >
        </div>
        <div>
            <label class="mb-1 block text-xs text-text-muted">Phone <span class="text-danger">*</span></label>
            <input
                type="tel"
                name="registrant[phone]"
                x-model="registrant.phone"
                value="{{ old('registrant.phone') }}"
                placeholder="+234.8012345678"
                autocomplete="tel"
                class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
            >
            <p class="mt-1 text-xs text-text-muted">International format, e.g. +234.8012345678</p>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-xs text-text-muted">Street address <span class="text-danger">*</span></label>
        <input
            type="text"
            name="registrant[address]"
            x-model="registrant.address"
            value="{{ old('registrant.address') }}"
            autocomplete="street-address"
            class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
        >
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs text-text-muted">City <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[city]"
                x-model="registrant.city"
                value="{{ old('registrant.city') }}"
                autocomplete="address-level2"
                class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
            >
        </div>
        <div>
            <label class="mb-1 block text-xs text-text-muted">State / region <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[state]"
                x-model="registrant.state"
                value="{{ old('registrant.state') }}"
                autocomplete="address-level1"
                class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
            >
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs text-text-muted">Postal / ZIP code <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[zip]"
                x-model="registrant.zip"
                value="{{ old('registrant.zip') }}"
                autocomplete="postal-code"
                class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
            >
        </div>
        <div>
            <label class="mb-1 block text-xs text-text-muted">Country <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[country]"
                x-model="registrant.country"
                value="{{ old('registrant.country', 'NG') }}"
                maxlength="2"
                placeholder="NG"
                autocomplete="country"
                class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm uppercase"
            >
            <p class="mt-1 text-xs text-text-muted">2-letter code (e.g. NG, US, GB)</p>
        </div>
    </div>
</div>
