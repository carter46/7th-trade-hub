@extends('layouts.dashboard-admin')

@section('title', 'Platform Settings')

@section('content')
<x-layout.page
    title="Platform Settings"
    subtitle="Configure fees, contact details, live chat, and verify email delivery."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Settings', null],
    ]"
>
    <div class="space-y-6">
        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-4">Fees & limits</h2>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <x-dashboard.input
                    name="platform_fee_percent"
                    type="number"
                    label="Platform fee (%)"
                    step="0.01"
                    :value="old('platform_fee_percent', $platformFeePercent)"
                    hint="Deducted from escrow release to seller."
                    required
                />
                <x-dashboard.input
                    name="deposit_min_amount"
                    type="number"
                    label="Minimum deposit (NGN)"
                    :value="old('deposit_min_amount', $depositMinAmount)"
                    required
                />
                <x-dashboard.input
                    name="withdrawal_min_amount"
                    type="number"
                    label="Minimum withdrawal (NGN)"
                    :value="old('withdrawal_min_amount', $withdrawalMinAmount)"
                    required
                />
                <x-dashboard.input
                    name="withdrawal_max_amount"
                    type="number"
                    label="Maximum withdrawal (NGN)"
                    :value="old('withdrawal_max_amount', $withdrawalMaxAmount)"
                    required
                />

                <hr class="border-border-subtle my-6">

                <h2 class="text-lg font-semibold text-text-primary mb-1">Live chat & contact</h2>
                <p class="text-sm text-text-secondary mb-4">Shown on the public Contact page. Only one chat provider can be active.</p>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2" for="live_chat_provider">Live chat provider</label>
                    <select
                        id="live_chat_provider"
                        name="live_chat_provider"
                        class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
                    >
                        <option value="none" @selected(old('live_chat_provider', $liveChatProvider) === 'none')>Off</option>
                        <option value="smartsupp" @selected(old('live_chat_provider', $liveChatProvider) === 'smartsupp')>Smartsupp</option>
                        <option value="jivo" @selected(old('live_chat_provider', $liveChatProvider) === 'jivo')>JivoChat</option>
                    </select>
                    @error('live_chat_provider')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <x-dashboard.input
                    name="smartsupp_key"
                    type="text"
                    label="Smartsupp key"
                    :value="old('smartsupp_key', $smartsuppKey)"
                    hint="Required when provider is Smartsupp."
                />
                <x-dashboard.input
                    name="jivo_widget_id"
                    type="text"
                    label="Jivo widget ID"
                    :value="old('jivo_widget_id', $jivoWidgetId)"
                    hint="Required when provider is Jivo. Paste widget ID or install URL."
                />

                <x-dashboard.input
                    name="contact_phone"
                    type="text"
                    label="Contact phone"
                    :value="old('contact_phone', $contactPhone)"
                />
                <x-dashboard.input
                    name="contact_email"
                    type="email"
                    label="Contact email"
                    :value="old('contact_email', $contactEmail)"
                />
                <x-dashboard.input
                    name="contact_email_alt"
                    type="email"
                    label="Alternate contact email"
                    :value="old('contact_email_alt', $contactEmailAlt)"
                />

                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save settings</x-dashboard.button>
            </form>
        </x-dashboard.card>

        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Analytics & tracking</h2>
            <p class="text-sm text-text-secondary mb-4">
                Optional Google Analytics and Microsoft Clarity integration. Scripts load only when enabled.
            </p>

            @error('analytics_connection')
                <p class="mb-4 text-sm text-danger">{{ $message }}</p>
            @enderror

            <form method="POST" action="{{ route('admin.settings.analytics') }}" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="text-base font-semibold text-text-primary">Google Analytics</h3>
                    <input type="hidden" name="google_enabled" value="0">
                    <x-dashboard.toggle
                        name="google_enabled"
                        label="Enable Google Analytics"
                        :checked="old('google_enabled', $analyticsGoogle->enabled)"
                        value="1"
                    />
                    <x-dashboard.input
                        name="google_measurement_id"
                        type="text"
                        label="Measurement ID"
                        :value="old('google_measurement_id', $analyticsGoogle->credential('measurement_id'))"
                        hint="Format: G-XXXXXXXXXX"
                    />
                    <x-dashboard.input
                        name="google_property_id"
                        type="text"
                        label="Property ID (optional)"
                        :value="old('google_property_id', $analyticsGoogle->credential('property_id'))"
                    />
                </div>

                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="text-base font-semibold text-text-primary">Microsoft Clarity</h3>
                    <input type="hidden" name="clarity_enabled" value="0">
                    <x-dashboard.toggle
                        name="clarity_enabled"
                        label="Enable Microsoft Clarity"
                        :checked="old('clarity_enabled', $analyticsClarity->enabled)"
                        value="1"
                    />
                    <x-dashboard.input
                        name="clarity_project_id"
                        type="text"
                        label="Project ID"
                        :value="old('clarity_project_id', $analyticsClarity->credential('project_id'))"
                    />
                </div>

                <x-dashboard.button type="submit" variant="primary" x-bind:disabled="submitting">Save analytics settings</x-dashboard.button>
            </form>

            <div class="mt-6 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.settings.analytics.test') }}">
                    @csrf
                    <input type="hidden" name="provider" value="google_analytics">
                    <input type="hidden" name="google_measurement_id" value="{{ old('google_measurement_id', $analyticsGoogle->credential('measurement_id')) }}">
                    <input type="hidden" name="google_property_id" value="{{ old('google_property_id', $analyticsGoogle->credential('property_id')) }}">
                    <x-dashboard.button type="submit" variant="secondary">Test GA connection</x-dashboard.button>
                </form>
                <form method="POST" action="{{ route('admin.settings.analytics.test') }}">
                    @csrf
                    <input type="hidden" name="provider" value="microsoft_clarity">
                    <input type="hidden" name="clarity_project_id" value="{{ old('clarity_project_id', $analyticsClarity->credential('project_id')) }}">
                    <x-dashboard.button type="submit" variant="secondary">Test Clarity connection</x-dashboard.button>
                </form>
            </div>
        </x-dashboard.card>

        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Email configuration</h2>
            <p class="text-sm text-text-secondary mb-4">
                Read-only status from <code class="text-xs">.env</code> mail settings. Use the form below to send a test message.
            </p>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm mb-6">
                <div class="rounded-lg bg-muted/40 border border-border-subtle p-3">
                    <dt class="text-text-muted text-xs uppercase tracking-wider">Mailer</dt>
                    <dd class="text-text-primary mt-1">{{ $mailStatus['mailer'] ?? '—' }}</dd>
                </div>
                <div class="rounded-lg bg-muted/40 border border-border-subtle p-3">
                    <dt class="text-text-muted text-xs uppercase tracking-wider">Host</dt>
                    <dd class="text-text-primary mt-1">{{ $mailStatus['host'] ?: '—' }}</dd>
                </div>
                <div class="rounded-lg bg-muted/40 border border-border-subtle p-3">
                    <dt class="text-text-muted text-xs uppercase tracking-wider">Port</dt>
                    <dd class="text-text-primary mt-1">{{ $mailStatus['port'] ?: '—' }}</dd>
                </div>
                <div class="rounded-lg bg-muted/40 border border-border-subtle p-3">
                    <dt class="text-text-muted text-xs uppercase tracking-wider">Encryption</dt>
                    <dd class="text-text-primary mt-1">{{ $mailStatus['encryption'] ?: '—' }}</dd>
                </div>
                <div class="rounded-lg bg-muted/40 border border-border-subtle p-3">
                    <dt class="text-text-muted text-xs uppercase tracking-wider">From</dt>
                    <dd class="text-text-primary mt-1">{{ $mailStatus['from_name'] ?? '' }} &lt;{{ $mailStatus['from_address'] ?? '—' }}&gt;</dd>
                </div>
                <div class="rounded-lg bg-muted/40 border border-border-subtle p-3">
                    <dt class="text-text-muted text-xs uppercase tracking-wider">Credentials</dt>
                    <dd class="text-text-primary mt-1">
                        Username: {{ ! empty($mailStatus['username_set']) ? 'set' : 'not set' }} ·
                        Password: {{ ! empty($mailStatus['password_set']) ? 'set' : 'not set' }}
                    </dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('admin.settings.test-mail') }}" class="space-y-4" x-data="{ sending: false }" @submit="sending = true">
                @csrf
                <x-dashboard.input
                    name="test_email"
                    type="email"
                    label="Send test to"
                    :value="old('test_email', auth()->user()->email)"
                    required
                />
                <x-dashboard.input
                    name="test_subject"
                    type="text"
                    label="Subject (optional)"
                    :value="old('test_subject', '7th Trade Hub — test email')"
                />
                @error('test_email')
                    <p class="text-sm text-danger">{{ $message }}</p>
                @enderror
                <x-dashboard.button type="submit" variant="secondary" x-bind:disabled="sending">Send test email</x-dashboard.button>
            </form>
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
