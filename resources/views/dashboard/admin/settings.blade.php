@extends('layouts.dashboard-admin')

@section('title', 'Platform Settings')

@section('content')
@php
    $chatProvider = old('live_chat_provider', $liveChat['provider'] ?? 'none');
    $smartsuppKeySet = (bool) ($liveChat['key_set'] ?? false) && ($liveChat['provider'] ?? '') === 'smartsupp';
    $jivoId = old('jivo_widget_id', ($liveChat['provider'] ?? '') === 'jivo' ? ($liveChat['credentials']['widget_id'] ?? '') : '');
    $chatwayId = old('chatway_widget_id', ($liveChat['provider'] ?? '') === 'chatway' ? ($liveChat['credentials']['widget_id'] ?? '') : '');
@endphp
<x-layout.page
    title="Platform Settings"
    subtitle="Site information, contact, social links, email providers, and analytics."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Settings', null],
    ]"
>
    <div class="space-y-6">
        {{-- Site Information --}}
        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Site information</h2>
            <p class="text-sm text-text-secondary mb-4">Name, heading, favicon, and light/dark logos used across the site and themes.</p>
            <form method="POST" action="{{ route('admin.settings.branding') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                    <div class="w-full"><x-dashboard.input name="site_name" label="Site name" :value="old('site_name', $branding['site_name'])" required /></div>
                    <div class="w-full"><x-dashboard.input name="site_short_name" label="Short name" :value="old('site_short_name', $branding['site_short_name'])" /></div>
                    <div class="w-full md:col-span-2"><x-dashboard.input name="heading" label="Heading" :value="old('heading', $branding['heading'])" /></div>
                    <div class="w-full md:col-span-2"><x-dashboard.input name="tagline" label="Tagline" :value="old('tagline', $branding['tagline'])" /></div>
                    <div class="w-full md:col-span-2"><x-dashboard.input name="meta_description" label="Meta description" :value="old('meta_description', $branding['meta_description'])" /></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full items-start">
                    <div class="w-full">
                        <x-dashboard.media-picker name="favicon_media_id" label="Favicon" :value="old('favicon_media_id', $branding['favicon_media_id'])" hint="Square icon for browser tabs." />
                    </div>
                    <div class="w-full">
                        <x-dashboard.media-picker name="logo_light_media_id" label="Light theme logo" :value="old('logo_light_media_id', $branding['logo_light_media_id'])" preview="wide" />
                    </div>
                    <div class="w-full">
                        <x-dashboard.media-picker name="logo_dark_media_id" label="Dark theme logo" :value="old('logo_dark_media_id', $branding['logo_dark_media_id'])" preview="wide" />
                    </div>
                </div>
                <x-dashboard.button type="submit" variant="primary">Save site information</x-dashboard.button>
            </form>
        </x-dashboard.card>

        {{-- Contact & live chat --}}
        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Platform contact</h2>
            <p class="text-sm text-text-secondary mb-4">Public phones and address for Contact Us and the footer contact block. Public emails are managed under Email identities.</p>
            <form method="POST" action="{{ route('admin.settings.contact') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-dashboard.input name="phone_support" label="Support phone" :value="old('phone_support', $contact['phone_support'])" />
                    <x-dashboard.input name="phone_general" label="General phone" :value="old('phone_general', $contact['phone_general'])" />
                    <x-dashboard.input name="phone_whatsapp" label="WhatsApp number" :value="old('phone_whatsapp', $contact['phone_whatsapp'])" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-dashboard.input name="address_street" label="Street" :value="old('address_street', $contact['address_street'])" />
                    <x-dashboard.input name="address_city" label="City" :value="old('address_city', $contact['address_city'])" />
                    <x-dashboard.input name="address_state" label="State" :value="old('address_state', $contact['address_state'])" />
                    <x-dashboard.input name="address_country" label="Country" :value="old('address_country', $contact['address_country'])" />
                    <x-dashboard.input name="address_postal" label="Postal code" :value="old('address_postal', $contact['address_postal'])" />
                    <x-dashboard.input name="timezone" label="Timezone" :value="old('timezone', $contact['timezone'])" />
                    <x-dashboard.input name="latitude" label="Latitude" :value="old('latitude', $contact['latitude'])" />
                    <x-dashboard.input name="longitude" label="Longitude" :value="old('longitude', $contact['longitude'])" />
                    <x-dashboard.input name="maps_url" label="Google Maps URL" :value="old('maps_url', $contact['maps_url'])" class="md:col-span-2" />
                    <x-dashboard.input name="maps_embed_url" label="Maps embed URL" :value="old('maps_embed_url', $contact['maps_embed_url'])" class="md:col-span-2" hint="Paste the iframe src URL for an embedded map." />
                    <x-dashboard.input name="support_hours" label="Support hours" :value="old('support_hours', $contact['support_hours'])" />
                    <x-dashboard.input name="business_hours" label="Business hours" :value="old('business_hours', $contact['business_hours'])" />
                    <x-dashboard.input name="registration_number" label="Registration number" :value="old('registration_number', $contact['registration_number'])" />
                    <x-dashboard.input name="vat_number" label="VAT" :value="old('vat_number', $contact['vat_number'])" />
                    <x-dashboard.input name="company_number" label="Company number" :value="old('company_number', $contact['company_number'])" />
                </div>

                <hr class="border-border-subtle my-4">
                <div class="space-y-4" x-data="{ provider: @js($chatProvider) }">
                    <h3 class="text-base font-semibold text-text-primary">Live chat</h3>
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2" for="live_chat_provider">Provider</label>
                        <select
                            id="live_chat_provider"
                            name="live_chat_provider"
                            x-model="provider"
                            class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
                        >
                            <option value="none">Off</option>
                            <option value="smartsupp">Smartsupp</option>
                            <option value="jivo">JivoChat</option>
                            <option value="chatway">Chatway</option>
                        </select>
                    </div>
                    <div x-show="provider === 'smartsupp'" x-cloak>
                        <x-dashboard.input name="smartsupp_key" type="password" label="Smartsupp key" hint="Leave blank to keep the current key. {{ $smartsuppKeySet ? 'Key is set.' : 'No key set.' }}" autocomplete="new-password" />
                    </div>
                    <div x-show="provider === 'jivo'" x-cloak>
                        <x-dashboard.input name="jivo_widget_id" label="Jivo widget ID" :value="$jivoId" hint="Widget ID only, or paste the full Jivo script URL." />
                    </div>
                    <div x-show="provider === 'chatway'" x-cloak>
                        <x-dashboard.input name="chatway_widget_id" label="Chatway widget ID" :value="$chatwayId" hint="Widget ID from Chatway install snippet." />
                    </div>
                    <p x-show="provider === 'none'" x-cloak class="text-sm text-text-muted">Live chat is off. Choose a provider to configure credentials.</p>
                </div>
                <x-dashboard.button type="submit" variant="primary">Save contact & live chat</x-dashboard.button>
            </form>
        </x-dashboard.card>

        {{-- Social links --}}
        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Social links</h2>
            <p class="text-sm text-text-secondary mb-4">Add platforms with optional custom logos. Without a logo, the footer uses built-in brand icons (or a letter fallback).</p>
            @php
                $socialRows = $socialLinks->values();
                $blankSlots = 5;
                $baseIndex = $socialRows->count();
            @endphp
            <form
                method="POST"
                action="{{ route('admin.settings.social') }}"
                class="space-y-4"
                x-data="{ visibleNew: {{ old('links.'.$baseIndex.'.platform') || old('links.'.$baseIndex.'.url') ? 1 : 0 }} }"
            >
                @csrf
                @foreach($socialRows as $i => $link)
                    <div
                        class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end border border-border-subtle rounded-lg p-4"
                        x-data="{ removed: false }"
                        x-show="!removed"
                    >
                        <input type="hidden" name="links[{{ $i }}][id]" value="{{ $link->id }}">
                        <input type="hidden" name="links[{{ $i }}][delete]" :value="removed ? '1' : '0'">
                        <div class="md:col-span-2 w-full">
                            <x-dashboard.input name="links[{{ $i }}][platform]" label="Platform" :value="old('links.'.$i.'.platform', $link->platform)" />
                        </div>
                        <div class="md:col-span-3 w-full">
                            <x-dashboard.input name="links[{{ $i }}][url]" label="URL" :value="old('links.'.$i.'.url', $link->url)" />
                        </div>
                        <div class="md:col-span-2 w-full">
                            <x-dashboard.input name="links[{{ $i }}][icon]" label="Icon key" :value="old('links.'.$i.'.icon', $link->icon)" hint="e.g. facebook" />
                        </div>
                        <div class="md:col-span-1 w-full">
                            <x-dashboard.input name="links[{{ $i }}][sort_order]" type="number" label="Sort" :value="old('links.'.$i.'.sort_order', $link->sort_order)" />
                        </div>
                        <div class="md:col-span-2 w-full">
                            <x-dashboard.media-picker name="links[{{ $i }}][icon_media_id]" label="Logo" :value="old('links.'.$i.'.icon_media_id', $link->icon_media_id)" />
                        </div>
                        <div class="md:col-span-2 flex flex-wrap items-center gap-3 pb-2">
                            <label class="inline-flex items-center gap-2 text-sm text-text-secondary">
                                <input type="hidden" name="links[{{ $i }}][enabled]" value="0">
                                <input type="checkbox" name="links[{{ $i }}][enabled]" value="1" @checked(old('links.'.$i.'.enabled', $link->enabled))>
                                Enabled
                            </label>
                            <button type="button" class="text-sm text-danger hover:underline" @click="removed = true">Remove</button>
                        </div>
                    </div>
                @endforeach

                @for($s = 0; $s < $blankSlots; $s++)
                    @php $idx = $baseIndex + $s; @endphp
                    <div
                        class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end border border-dashed border-border-subtle rounded-lg p-4"
                        x-show="visibleNew > {{ $s }}"
                        x-cloak
                    >
                        <div class="md:col-span-2 w-full">
                            <x-dashboard.input name="links[{{ $idx }}][platform]" label="Platform" :value="old('links.'.$idx.'.platform')" placeholder="e.g. Instagram" />
                        </div>
                        <div class="md:col-span-3 w-full">
                            <x-dashboard.input name="links[{{ $idx }}][url]" label="URL" :value="old('links.'.$idx.'.url')" />
                        </div>
                        <div class="md:col-span-2 w-full">
                            <x-dashboard.input name="links[{{ $idx }}][icon]" label="Icon key" :value="old('links.'.$idx.'.icon')" hint="e.g. instagram" />
                        </div>
                        <div class="md:col-span-1 w-full">
                            <x-dashboard.input name="links[{{ $idx }}][sort_order]" type="number" label="Sort" :value="old('links.'.$idx.'.sort_order', $idx)" />
                        </div>
                        <div class="md:col-span-2 w-full">
                            <x-dashboard.media-picker name="links[{{ $idx }}][icon_media_id]" label="Logo" :value="old('links.'.$idx.'.icon_media_id')" />
                        </div>
                        <div class="md:col-span-2 flex items-center pb-2">
                            <input type="hidden" name="links[{{ $idx }}][enabled]" value="1">
                            <span class="text-xs text-text-muted">New link</span>
                        </div>
                    </div>
                @endfor

                <div class="flex flex-wrap items-center gap-3">
                    <x-dashboard.button
                        type="button"
                        variant="secondary"
                        x-on:click="if (visibleNew < {{ $blankSlots }}) visibleNew++"
                        x-bind:disabled="visibleNew >= {{ $blankSlots }}"
                    >
                        Add social link
                    </x-dashboard.button>
                    <x-dashboard.button type="submit" variant="primary">Save social links</x-dashboard.button>
                </div>
            </form>
        </x-dashboard.card>

        {{-- Email --}}
        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Email</h2>
            <p class="text-sm text-text-secondary mb-4">Brevo API is primary. Laravel Mail is the fallback. Identities control From addresses.</p>

            @if($brevo->last_error)
                <x-dashboard.alert type="danger" class="mb-4" title="Last Brevo error">
                    <p class="text-sm break-words">{{ $brevo->last_error }}</p>
                    @if(!empty($brevo->meta['last_fallback_reason']))
                        <p class="text-sm mt-2">Fallback was used after: {{ $brevo->meta['last_fallback_reason'] }}</p>
                    @endif
                </x-dashboard.alert>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @foreach([
                    ['label' => 'Brevo', 'row' => $brevo],
                    ['label' => 'Laravel Mail', 'row' => $laravelMail],
                ] as $health)
                    <div class="rounded-xl border border-border-subtle p-4 text-sm">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="font-semibold text-text-primary">{{ $health['label'] }}</span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $health['row']->status === 'connected' || $health['row']->status === 'configured' ? 'bg-success' : ($health['row']->status === 'error' ? 'bg-danger' : 'bg-text-muted') }}"></span>
                                {{ ucfirst($health['row']->status ?: 'idle') }}
                            </span>
                        </div>
                        <dl class="space-y-1 text-text-secondary">
                            <div class="flex justify-between gap-2"><dt>Last success</dt><dd>{{ $health['row']->last_success_at?->diffForHumans() ?? '—' }}</dd></div>
                            <div class="flex justify-between gap-2"><dt>Last error</dt><dd>{{ $health['row']->last_error_at?->diffForHumans() ?? '—' }}</dd></div>
                            @if($health['row']->last_error)
                                <p class="text-danger text-xs mt-2 break-words">{{ $health['row']->last_error }}</p>
                            @endif
                        </dl>
                    </div>
                @endforeach
            </div>

            @if(isset($recentEmailFailures) && $recentEmailFailures->isNotEmpty())
                <div class="rounded-xl border border-border-subtle p-4 mb-6 space-y-2">
                    <h3 class="font-semibold text-text-primary">Recent send failures</h3>
                    @foreach($recentEmailFailures as $attempt)
                        <div class="text-sm text-text-secondary border-t border-border-subtle pt-2 first:border-0 first:pt-0">
                            <p class="text-text-primary">{{ $attempt->created_at?->diffForHumans() }} · {{ $attempt->provider }} · {{ $attempt->recipient ?? '—' }}@if($attempt->http_status) · HTTP {{ $attempt->http_status }}@endif</p>
                            <p class="text-danger break-words">{{ $attempt->error_message ?: 'Unknown error' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.email') }}" class="space-y-6">
                @csrf
                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="font-semibold text-text-primary">Brevo (primary)</h3>
                    <input type="hidden" name="brevo_enabled" value="0">
                    <x-dashboard.toggle name="brevo_enabled" label="Enable Brevo API" :checked="old('brevo_enabled', $brevo->enabled)" value="1" />
                    <x-dashboard.input name="brevo_api_key" type="password" label="API key" hint="Leave blank to keep the current key. {{ filled($brevo->credential('api_key')) ? 'Key is set.' : 'No key set.' }}" />
                </div>
                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="font-semibold text-text-primary">Laravel Mail (fallback)</h3>
                    <input type="hidden" name="laravel_mail_enabled" value="0">
                    <x-dashboard.toggle name="laravel_mail_enabled" label="Enable Laravel Mail fallback" :checked="old('laravel_mail_enabled', $laravelMail->enabled)" value="1" />
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2">Fallback mailer</label>
                        <select name="fallback_mailer" class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm">
                            <option value="smtp" @selected(old('fallback_mailer', $laravelMail->credential('mailer', 'smtp')) === 'smtp')>SMTP</option>
                            <option value="sendmail" @selected(old('fallback_mailer', $laravelMail->credential('mailer')) === 'sendmail')>Sendmail</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-dashboard.input name="smtp_host" label="SMTP host" :value="old('smtp_host', $laravelMail->credential('host', config('mail.mailers.smtp.host')))" />
                        <x-dashboard.input name="smtp_port" type="number" label="Port" :value="old('smtp_port', $laravelMail->credential('port', config('mail.mailers.smtp.port')))" />
                        <x-dashboard.input name="smtp_encryption" label="Encryption" :value="old('smtp_encryption', $laravelMail->credential('encryption', config('mail.mailers.smtp.encryption')))" />
                        <x-dashboard.input name="smtp_username" label="Username" :value="old('smtp_username', $laravelMail->credential('username', config('mail.mailers.smtp.username')))" />
                        <x-dashboard.input name="smtp_password" type="password" label="Password" hint="Leave blank to keep current password." />
                        <x-dashboard.input name="sendmail_path" label="Sendmail path" :value="old('sendmail_path', $laravelMail->credential('sendmail_path'))" />
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="font-semibold text-text-primary">Email identities</h3>
                    @foreach($emailIdentities as $identity)
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 border border-border-subtle rounded-lg p-3">
                            <div class="md:col-span-4 text-sm font-medium text-text-secondary uppercase tracking-wide">{{ $identity->profile }}</div>
                            <x-dashboard.input name="identities[{{ $identity->profile }}][from_name]" label="From name" :value="old('identities.'.$identity->profile.'.from_name', $identity->from_name)" />
                            <x-dashboard.input name="identities[{{ $identity->profile }}][from_email]" type="email" label="From email" :value="old('identities.'.$identity->profile.'.from_email', $identity->from_email)" />
                            <x-dashboard.input name="identities[{{ $identity->profile }}][reply_to_email]" type="email" label="Reply-To" :value="old('identities.'.$identity->profile.'.reply_to_email', $identity->reply_to_email)" />
                            <label class="flex items-center gap-2 text-sm text-text-secondary pb-2">
                                <input type="hidden" name="identities[{{ $identity->profile }}][enabled]" value="0">
                                <input type="checkbox" name="identities[{{ $identity->profile }}][enabled]" value="1" @checked(old('identities.'.$identity->profile.'.enabled', $identity->enabled))>
                                Enabled
                            </label>
                        </div>
                    @endforeach
                </div>

                <x-dashboard.button type="submit" variant="primary">Save email settings</x-dashboard.button>
            </form>

            <form
                method="POST"
                action="{{ route('admin.settings.test-mail') }}"
                class="mt-6 space-y-4 border-t border-border-subtle pt-6"
                x-data="{
                    sending: false,
                    status: '',
                    ok: null,
                    async submit(e) {
                        e.preventDefault();
                        if (this.sending) return;
                        this.sending = true;
                        this.status = '';
                        this.ok = null;
                        try {
                            const res = await fetch(e.target.action, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': e.target.querySelector('[name=_token]')?.value || '',
                                },
                                body: new FormData(e.target),
                            });
                            const data = await res.json().catch(() => ({}));
                            this.ok = !!data.ok;
                            if (res.ok && data.ok) {
                                this.status = data.message || 'Test email sent.';
                            } else if (data.errors?.test_email?.[0]) {
                                this.status = data.errors.test_email[0];
                            } else {
                                this.status = data.message || data.error || 'Mail send failed.';
                            }
                        } catch (err) {
                            this.ok = false;
                            this.status = err?.message || 'Mail send failed.';
                        } finally {
                            this.sending = false;
                        }
                    }
                }"
                @submit="submit"
            >
                @csrf
                <h3 class="font-semibold text-text-primary">Send test email</h3>
                <x-dashboard.input name="test_email" type="email" label="Send test to" :value="old('test_email', auth()->user()->email)" required />
                <x-dashboard.input name="test_subject" label="Subject (optional)" :value="old('test_subject', $siteName.' — test email')" />
                <p
                    x-show="status"
                    x-text="status"
                    class="text-sm break-words"
                    :class="ok === true ? 'text-success' : 'text-danger'"
                    x-cloak
                ></p>
                @error('test_email')
                    <p class="text-sm text-danger" x-show="!status">{{ $message }}</p>
                @enderror
                <x-dashboard.button type="submit" variant="secondary" x-bind:disabled="sending">
                    <span x-show="!sending">Send test email</span>
                    <span x-show="sending" x-cloak>Sending…</span>
                </x-dashboard.button>
            </form>
        </x-dashboard.card>

        {{-- Analytics (kept) --}}
        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Analytics & tracking</h2>
            <p class="text-sm text-text-secondary mb-4">Optional Google Analytics and Microsoft Clarity. Scripts load only when enabled.</p>
            @error('analytics_connection')
                <p class="mb-4 text-sm text-danger">{{ $message }}</p>
            @enderror
            <form method="POST" action="{{ route('admin.settings.analytics') }}" class="space-y-6">
                @csrf
                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="text-base font-semibold text-text-primary">Google Analytics</h3>
                    <input type="hidden" name="google_enabled" value="0">
                    <x-dashboard.toggle name="google_enabled" label="Enable Google Analytics" :checked="old('google_enabled', $analyticsGoogle->enabled)" value="1" />
                    <x-dashboard.input name="google_measurement_id" label="Measurement ID" :value="old('google_measurement_id', $analyticsGoogle->credential('measurement_id'))" hint="Format: G-XXXXXXXXXX" />
                    <x-dashboard.input name="google_property_id" label="Property ID (optional)" :value="old('google_property_id', $analyticsGoogle->credential('property_id'))" />
                </div>
                <div class="rounded-xl border border-border-subtle p-4 space-y-4">
                    <h3 class="text-base font-semibold text-text-primary">Microsoft Clarity</h3>
                    <input type="hidden" name="clarity_enabled" value="0">
                    <x-dashboard.toggle name="clarity_enabled" label="Enable Microsoft Clarity" :checked="old('clarity_enabled', $analyticsClarity->enabled)" value="1" />
                    <x-dashboard.input name="clarity_project_id" label="Project ID" :value="old('clarity_project_id', $analyticsClarity->credential('project_id'))" />
                </div>
                <x-dashboard.button type="submit" variant="primary">Save analytics settings</x-dashboard.button>
            </form>
            <div class="mt-6 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.settings.analytics.test') }}">
                    @csrf
                    <input type="hidden" name="provider" value="google_analytics">
                    <x-dashboard.button type="submit" variant="secondary">Test GA connection</x-dashboard.button>
                </form>
                <form method="POST" action="{{ route('admin.settings.analytics.test') }}">
                    @csrf
                    <input type="hidden" name="provider" value="microsoft_clarity">
                    <x-dashboard.button type="submit" variant="secondary">Test Clarity connection</x-dashboard.button>
                </form>
            </div>
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
