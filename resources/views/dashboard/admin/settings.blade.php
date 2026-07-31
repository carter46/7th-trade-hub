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
            <form method="POST" action="{{ route('admin.settings.branding') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-dashboard.input name="site_name" label="Site name" :value="old('site_name', $branding['site_name'])" required />
                    <x-dashboard.input name="site_short_name" label="Short name" :value="old('site_short_name', $branding['site_short_name'])" />
                    <x-dashboard.input name="heading" label="Heading" :value="old('heading', $branding['heading'])" class="md:col-span-2" />
                    <x-dashboard.input name="tagline" label="Tagline" :value="old('tagline', $branding['tagline'])" class="md:col-span-2" />
                    <x-dashboard.input name="meta_description" label="Meta description" :value="old('meta_description', $branding['meta_description'])" class="md:col-span-2" />
                    <x-dashboard.media-picker name="favicon_media_id" label="Favicon" :value="old('favicon_media_id', $branding['favicon_media_id'])" />
                    <div></div>
                    <x-dashboard.media-picker name="logo_light_media_id" label="Light theme logo" :value="old('logo_light_media_id', $branding['logo_light_media_id'])" preview="wide" />
                    <x-dashboard.media-picker name="logo_dark_media_id" label="Dark theme logo" :value="old('logo_dark_media_id', $branding['logo_dark_media_id'])" preview="wide" />
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
                <h3 class="text-base font-semibold text-text-primary">Live chat</h3>
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2" for="live_chat_provider">Provider</label>
                    <select id="live_chat_provider" name="live_chat_provider" class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm">
                        <option value="none" @selected($chatProvider === 'none')>Off</option>
                        <option value="smartsupp" @selected($chatProvider === 'smartsupp')>Smartsupp</option>
                        <option value="jivo" @selected($chatProvider === 'jivo')>JivoChat</option>
                        <option value="chatway" @selected($chatProvider === 'chatway')>Chatway</option>
                    </select>
                </div>
                <x-dashboard.input name="smartsupp_key" type="password" label="Smartsupp key" hint="Leave blank to keep the current key. {{ $smartsuppKeySet ? 'Key is set.' : 'No key set.' }}" autocomplete="new-password" />
                <x-dashboard.input name="jivo_widget_id" label="Jivo widget ID" :value="$jivoId" hint="Widget ID only, or paste the full Jivo script URL." />
                <x-dashboard.input name="chatway_widget_id" label="Chatway widget ID" :value="$chatwayId" hint="Widget ID from Chatway install snippet." />
                <x-dashboard.button type="submit" variant="primary">Save contact & live chat</x-dashboard.button>
            </form>
        </x-dashboard.card>

        {{-- Social links --}}
        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Social links</h2>
            <p class="text-sm text-text-secondary mb-4">Add any platform. Only enabled links with URLs appear in the footer and Contact page.</p>
            <form method="POST" action="{{ route('admin.settings.social') }}" class="space-y-4">
                @csrf
                @foreach($socialLinks as $i => $link)
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end border border-border-subtle rounded-lg p-3">
                        <input type="hidden" name="links[{{ $i }}][id]" value="{{ $link->id }}">
                        <x-dashboard.input name="links[{{ $i }}][platform]" label="Platform" :value="old('links.'.$i.'.platform', $link->platform)" />
                        <x-dashboard.input name="links[{{ $i }}][url]" label="URL" :value="old('links.'.$i.'.url', $link->url)" class="md:col-span-2" />
                        <x-dashboard.input name="links[{{ $i }}][icon]" label="Icon key" :value="old('links.'.$i.'.icon', $link->icon)" />
                        <x-dashboard.input name="links[{{ $i }}][sort_order]" type="number" label="Sort" :value="old('links.'.$i.'.sort_order', $link->sort_order)" />
                        <label class="flex items-center gap-2 text-sm text-text-secondary pb-2">
                            <input type="checkbox" name="links[{{ $i }}][enabled]" value="1" @checked(old('links.'.$i.'.enabled', $link->enabled))>
                            Enabled
                        </label>
                        <label class="flex items-center gap-2 text-sm text-danger pb-2 md:col-span-6">
                            <input type="checkbox" name="links[{{ $i }}][delete]" value="1">
                            Delete this link
                        </label>
                    </div>
                @endforeach
                @php $n = $socialLinks->count(); @endphp
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 border border-dashed border-border-subtle rounded-lg p-3">
                    <x-dashboard.input name="links[{{ $n }}][platform]" label="New platform" :value="old('links.'.$n.'.platform')" placeholder="e.g. Instagram" />
                    <x-dashboard.input name="links[{{ $n }}][url]" label="URL" :value="old('links.'.$n.'.url')" class="md:col-span-2" />
                    <x-dashboard.input name="links[{{ $n }}][sort_order]" type="number" label="Sort" :value="old('links.'.$n.'.sort_order', $n)" />
                    <input type="hidden" name="links[{{ $n }}][enabled]" value="1">
                </div>
                <x-dashboard.button type="submit" variant="primary">Save social links</x-dashboard.button>
            </form>
        </x-dashboard.card>

        {{-- Email --}}
        <x-dashboard.card variant="solid">
            <h2 class="text-lg font-semibold text-text-primary mb-1">Email</h2>
            <p class="text-sm text-text-secondary mb-4">Brevo API is primary. Laravel Mail is the fallback. Identities control From addresses.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @foreach([
                    ['label' => 'Brevo', 'row' => $brevo],
                    ['label' => 'Laravel Mail', 'row' => $laravelMail],
                    ['label' => 'Google Analytics', 'row' => $analyticsGoogle],
                    ['label' => 'Microsoft Clarity', 'row' => $analyticsClarity],
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
                            <div class="flex justify-between gap-2"><dt>Last test</dt><dd>{{ $health['row']->last_tested_at?->diffForHumans() ?? '—' }}</dd></div>
                            <div class="flex justify-between gap-2"><dt>Last success</dt><dd>{{ $health['row']->last_success_at?->diffForHumans() ?? '—' }}</dd></div>
                            <div class="flex justify-between gap-2"><dt>Success rate</dt><dd>{{ $health['row']->successRate() !== null ? $health['row']->successRate().'%' : '—' }}</dd></div>
                            <div class="flex justify-between gap-2"><dt>Avg response</dt><dd>{{ $health['row']->avg_latency_ms ? $health['row']->avg_latency_ms.' ms' : '—' }}</dd></div>
                            @if($health['row']->last_error)
                                <p class="text-danger text-xs mt-2 break-words">{{ $health['row']->last_error }}</p>
                            @endif
                        </dl>
                    </div>
                @endforeach
            </div>

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

            <form method="POST" action="{{ route('admin.settings.test-mail') }}" class="mt-6 space-y-4 border-t border-border-subtle pt-6">
                @csrf
                <h3 class="font-semibold text-text-primary">Send test email</h3>
                <x-dashboard.input name="test_email" type="email" label="Send test to" :value="old('test_email', auth()->user()->email)" required />
                <x-dashboard.input name="test_subject" label="Subject (optional)" :value="old('test_subject', $siteName.' — test email')" />
                @error('test_email')
                    <p class="text-sm text-danger">{{ $message }}</p>
                @enderror
                <x-dashboard.button type="submit" variant="secondary">Send test email</x-dashboard.button>
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
