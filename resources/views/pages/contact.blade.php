@extends('layouts.marketing')

@section('title', 'Contact Us')

@section('content')
@php
    $c = $contact ?? [];
    $chatOn = (bool) ($chatEnabled ?? false);
    $chatLabel = $liveChat['label'] ?? 'Live chat';
    $supportHref = route('dashboard.support.create');
    $ticketsHref = auth()->check()
        ? route('dashboard.support.index')
        : route('login');
    $wa = preg_replace('/\D+/', '', (string) ($c['phone_whatsapp'] ?? ''));
    $mapsEmbed = trim((string) ($c['maps_embed_url'] ?? ''));
    if ($mapsEmbed === '' && filled($c['latitude'] ?? null) && filled($c['longitude'] ?? null)) {
        $mapsEmbed = 'https://maps.google.com/maps?q='.urlencode($c['latitude'].','.$c['longitude']).'&z=15&output=embed';
    }
@endphp

@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Contact'],
    ],
    'title' => 'Contact & Support',
    'subtitle' => 'Reach our team for wallet funding, crypto sells, marketplace orders, KYC, and account help.',
    'image' => 'assets/images/helpcenter.jpg',
    'cta' => [
        'href' => route('help'),
        'label' => 'Browse Help Center',
    ],
])

<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-16 sm:pb-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        <div class="lg:col-span-8 space-y-6 sm:space-y-8">
            <div>
                <h2 class="font-display text-xl sm:text-2xl font-semibold text-white mb-2">Direct contact methods</h2>
                <p class="text-sm sm:text-base text-text-secondary max-w-2xl leading-relaxed">
                    Email, phone, WhatsApp, and tickets — plus live chat when configured.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                <div class="p-6 sm:p-8 rounded-xl border border-border-subtle bg-elevated">
                    <h3 class="font-display text-lg font-semibold text-white mb-4">Email</h3>
                    <ul class="space-y-3 text-sm">
                        @foreach([
                            'Info' => $c['email_info'] ?? '',
                            'Support' => $c['email_support'] ?? '',
                            'Sales' => $c['email_sales'] ?? '',
                        ] as $label => $email)
                            @if($email !== '')
                                <li>
                                    <span class="text-text-muted block text-xs uppercase tracking-wide">{{ $label }}</span>
                                    <a href="mailto:{{ $email }}" class="text-accent font-semibold hover:underline break-all">{{ $email }}</a>
                                </li>
                            @endif
                        @endforeach
                        @if(($c['email_info'] ?? '') === '' && ($c['email_support'] ?? '') === '' && ($c['email_sales'] ?? '') === '')
                            <li class="text-text-muted">Emails appear once set in Admin → Settings.</li>
                        @endif
                    </ul>
                </div>

                @if($chatOn)
                <div id="live-chat" class="p-6 sm:p-8 rounded-xl border border-border-subtle bg-elevated border-l-4 border-l-accent scroll-mt-28">
                    <h3 class="font-display text-lg font-semibold text-white mb-1">Chat with us</h3>
                    <p class="text-xs text-text-muted mb-4">{{ $chatLabel }} · Available</p>
                    <p class="text-sm text-text-secondary mb-5">Use the chat bubble or open the widget for time-sensitive help.</p>
                    <button type="button" onclick="document.dispatchEvent(new CustomEvent('open-live-chat'))"
                        class="w-full py-3 rounded-xl bg-primary/15 border border-accent/30 text-accent font-semibold hover:bg-primary hover:text-white transition-colors">
                        Open chat
                    </button>
                </div>
                @endif

                <div class="p-6 sm:p-8 rounded-xl border border-border-subtle bg-elevated">
                    <h3 class="font-display text-lg font-semibold text-white mb-4">Phone & WhatsApp</h3>
                    <ul class="space-y-3 text-sm">
                        @if(($c['phone_support'] ?? '') !== '')
                            <li>
                                <span class="text-text-muted block text-xs uppercase tracking-wide">Call (support)</span>
                                <a href="tel:{{ preg_replace('/\s+/', '', $c['phone_support']) }}" class="text-white font-semibold hover:text-accent">{{ $c['phone_support'] }}</a>
                            </li>
                        @endif
                        @if(($c['phone_general'] ?? '') !== '' && ($c['phone_general'] ?? '') !== ($c['phone_support'] ?? ''))
                            <li>
                                <span class="text-text-muted block text-xs uppercase tracking-wide">Call (general)</span>
                                <a href="tel:{{ preg_replace('/\s+/', '', $c['phone_general']) }}" class="text-white font-semibold hover:text-accent">{{ $c['phone_general'] }}</a>
                            </li>
                        @endif
                        @if($wa !== '')
                            <li>
                                <span class="text-text-muted block text-xs uppercase tracking-wide">WhatsApp</span>
                                <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="text-accent font-semibold hover:underline">{{ $c['phone_whatsapp'] }}</a>
                            </li>
                        @endif
                        @if(($c['phone_support'] ?? '') === '' && ($c['phone_general'] ?? '') === '' && $wa === '')
                            <li class="text-text-muted">Phone numbers appear once set in Admin → Settings.</li>
                        @endif
                    </ul>
                    @if(($c['support_hours'] ?? '') !== '' || ($c['business_hours'] ?? '') !== '')
                        <p class="mt-4 text-xs text-text-muted">
                            {{ $c['business_hours'] ?: $c['support_hours'] }}
                            @if(($c['timezone'] ?? '') !== '') · {{ $c['timezone'] }} @endif
                        </p>
                    @endif
                </div>

                <div class="p-6 sm:p-8 rounded-xl border border-border-subtle bg-elevated">
                    <h3 class="font-display text-lg font-semibold text-white mb-4">Support tickets</h3>
                    <p class="text-sm text-text-secondary mb-5">Create a ticket from your dashboard. You must be logged in.</p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ $supportHref }}" class="flex-1 text-center py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-accent transition-colors">New ticket</a>
                        <a href="{{ $ticketsHref }}" class="flex-1 text-center py-2.5 rounded-lg bg-white/5 border border-border-subtle text-sm font-semibold text-white hover:bg-white/10 transition-colors">My tickets</a>
                    </div>
                </div>
            </div>

            @if($formattedAddress !== '' || $mapsEmbed !== '')
                <div class="rounded-xl border border-border-subtle bg-elevated overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <h3 class="font-display text-lg font-semibold text-white mb-2">Address</h3>
                        @if($formattedAddress !== '')
                            <p class="text-sm text-text-secondary">{{ $formattedAddress }}</p>
                        @endif
                        @if(($c['maps_url'] ?? '') !== '')
                            <a href="{{ $c['maps_url'] }}" target="_blank" rel="noopener" class="inline-block mt-3 text-sm text-accent hover:underline">Open in Google Maps</a>
                        @endif
                    </div>
                    @if($mapsEmbed !== '')
                        <div class="aspect-[16/9] w-full bg-muted">
                            <iframe title="Map" src="{{ $mapsEmbed }}" class="w-full h-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    @endif
                </div>
            @endif

            @if(($socialLinks ?? collect())->isNotEmpty())
                <div>
                    <h3 class="font-display text-lg font-semibold text-white mb-4">Social</h3>
                    <x-ui.social-links :links="$socialLinks" />
                </div>
            @endif
        </div>

        <aside class="lg:col-span-4">
            <div class="lg:sticky lg:top-28 space-y-5">
                <div class="glassmorphism rounded-xl p-6 sm:p-7">
                    <h3 class="font-display text-lg font-semibold text-white mb-5">Quick links</h3>
                    <ul class="space-y-1">
                        <li><a href="{{ route('help') }}" class="flex justify-between p-3 rounded-lg hover:bg-white/5 text-sm text-text-secondary hover:text-white">Help Center</a></li>
                        <li><a href="{{ route('legal') }}" class="flex justify-between p-3 rounded-lg hover:bg-white/5 text-sm text-text-secondary hover:text-white">Legal hub</a></li>
                        <li><a href="{{ route('exchange') }}" class="flex justify-between p-3 rounded-lg hover:bg-white/5 text-sm text-text-secondary hover:text-white">Exchange</a></li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>
</section>

@if($chatOn)
<script>
document.addEventListener('open-live-chat', function () {
    try { if (typeof window.smartsupp === 'function') window.smartsupp('chat:open'); } catch (e) {}
    try { if (window.jivo_api && typeof window.jivo_api.open === 'function') window.jivo_api.open(); } catch (e) {}
    try {
        if (window.$chatway && typeof window.$chatway.openChatwayWidget === 'function') {
            window.$chatway.openChatwayWidget();
            return;
        }
    } catch (e) {}
    var chatSection = document.getElementById('live-chat');
    if (chatSection) chatSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
});
</script>
@endif
@endsection
