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
                @input="validateRegistrantFields()"
                value="{{ old('registrant.first_name') }}"
                autocomplete="given-name"
                :class="registrantErrors.first_name ? 'border-danger' : 'border-border-default'"
                class="w-full rounded-lg bg-elevated text-text-primary text-sm"
            >
            <p x-show="registrantErrors.first_name" x-cloak class="mt-1 text-xs text-danger" x-text="registrantErrors.first_name"></p>
        </div>
        <div>
            <label class="mb-1 block text-xs text-text-muted">Last name <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[last_name]"
                x-model="registrant.last_name"
                @input="validateRegistrantFields()"
                value="{{ old('registrant.last_name') }}"
                autocomplete="family-name"
                :class="registrantErrors.last_name ? 'border-danger' : 'border-border-default'"
                class="w-full rounded-lg bg-elevated text-text-primary text-sm"
            >
            <p x-show="registrantErrors.last_name" x-cloak class="mt-1 text-xs text-danger" x-text="registrantErrors.last_name"></p>
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
                @input="validateRegistrantFields()"
                value="{{ old('registrant.email', auth()->user()->email) }}"
                autocomplete="email"
                :class="registrantErrors.email ? 'border-danger' : 'border-border-default'"
                class="w-full rounded-lg bg-elevated text-text-primary text-sm"
            >
            <p x-show="registrantErrors.email" x-cloak class="mt-1 text-xs text-danger" x-text="registrantErrors.email"></p>
        </div>
        <div>
            <label class="mb-1 block text-xs text-text-muted">Phone <span class="text-danger">*</span></label>
            <input
                type="tel"
                name="registrant[phone]"
                x-model="registrant.phone"
                @input="validateRegistrantFields()"
                value="{{ old('registrant.phone') }}"
                placeholder="+234.8012345678"
                autocomplete="tel"
                :class="registrantErrors.phone ? 'border-danger' : 'border-border-default'"
                class="w-full rounded-lg bg-elevated text-text-primary text-sm"
            >
            <p x-show="registrantErrors.phone" x-cloak class="mt-1 text-xs text-danger" x-text="registrantErrors.phone"></p>
            <p x-show="!registrantErrors.phone" class="mt-1 text-xs text-text-muted">International format, e.g. +234.8012345678</p>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-xs text-text-muted">Street address <span class="text-danger">*</span></label>
        <input
            type="text"
            name="registrant[address]"
            x-model="registrant.address"
            @input="validateRegistrantFields()"
            value="{{ old('registrant.address') }}"
            autocomplete="street-address"
            :class="registrantErrors.address ? 'border-danger' : 'border-border-default'"
            class="w-full rounded-lg bg-elevated text-text-primary text-sm"
        >
        <p x-show="registrantErrors.address" x-cloak class="mt-1 text-xs text-danger" x-text="registrantErrors.address"></p>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs text-text-muted">City <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[city]"
                x-model="registrant.city"
                @input="validateRegistrantFields()"
                value="{{ old('registrant.city') }}"
                autocomplete="address-level2"
                :class="registrantErrors.city ? 'border-danger' : 'border-border-default'"
                class="w-full rounded-lg bg-elevated text-text-primary text-sm"
            >
            <p x-show="registrantErrors.city" x-cloak class="mt-1 text-xs text-danger" x-text="registrantErrors.city"></p>
        </div>
        <div>
            <label class="mb-1 block text-xs text-text-muted">State / region <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[state]"
                x-model="registrant.state"
                @input="validateRegistrantFields()"
                value="{{ old('registrant.state') }}"
                autocomplete="address-level1"
                :class="registrantErrors.state ? 'border-danger' : 'border-border-default'"
                class="w-full rounded-lg bg-elevated text-text-primary text-sm"
            >
            <p x-show="registrantErrors.state" x-cloak class="mt-1 text-xs text-danger" x-text="registrantErrors.state"></p>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs text-text-muted">Postal / ZIP code <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[zip]"
                x-model="registrant.zip"
                @input="validateRegistrantFields()"
                value="{{ old('registrant.zip') }}"
                autocomplete="postal-code"
                :class="registrantErrors.zip ? 'border-danger' : 'border-border-default'"
                class="w-full rounded-lg bg-elevated text-text-primary text-sm"
            >
            <p x-show="registrantErrors.zip" x-cloak class="mt-1 text-xs text-danger" x-text="registrantErrors.zip"></p>
        </div>
        <div>
            <label class="mb-1 block text-xs text-text-muted">Country <span class="text-danger">*</span></label>
            <input
                type="text"
                name="registrant[country]"
                x-model="registrant.country"
                @input="validateRegistrantFields()"
                value="{{ old('registrant.country', 'NG') }}"
                maxlength="2"
                placeholder="NG"
                autocomplete="country"
                :class="registrantErrors.country ? 'border-danger' : 'border-border-default'"
                class="w-full rounded-lg bg-elevated text-text-primary text-sm uppercase"
            >
            <p x-show="registrantErrors.country" x-cloak class="mt-1 text-xs text-danger" x-text="registrantErrors.country"></p>
            <p x-show="!registrantErrors.country" class="mt-1 text-xs text-text-muted">2-letter code (e.g. NG, US, GB)</p>
        </div>
    </div>
</div>
