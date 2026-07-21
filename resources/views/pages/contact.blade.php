@extends('layouts.marketing')

@section('title', 'Contact Us | 7th Trade Hub')

@section('content')
@php
    $phone = trim((string) ($contactPhone ?? ''));
    $email = trim((string) ($contactEmail ?? ''));
    $emailAlt = trim((string) ($contactEmailAlt ?? ''));
    $chatOn = (bool) ($chatEnabled ?? false);
    $supportHref = auth()->check()
        ? route('dashboard.support.create')
        : route('login');
    $ticketsHref = auth()->check()
        ? route('dashboard.support.index')
        : route('login');
@endphp

@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Contact'],
    ],
    'title' => 'Contact & Support',
    'subtitle' => 'Reach our team for wallet funding, crypto sells, marketplace orders, KYC, and account help — by email, phone, or live chat.',
    'image' => 'assets/images/helpcenter.jpg',
    'cta' => [
        'href' => route('help'),
        'label' => 'Browse Help Center',
    ],
])

<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-16 sm:pb-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        {{-- Direct contact methods --}}
        <div class="lg:col-span-8 space-y-6 sm:space-y-8">
            <div>
                <h2 class="font-display text-xl sm:text-2xl font-semibold text-white mb-2">Direct contact methods</h2>
                <p class="text-sm sm:text-base text-text-secondary max-w-2xl leading-relaxed">
                    Choose the channel that fits your issue. Live chat appears on this page when enabled; email and phone use the details configured by our team.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                {{-- Email --}}
                <div class="p-6 sm:p-8 rounded-xl border border-border-subtle bg-elevated hover:bg-muted/40 transition-colors">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-accent">
                            <x-ui.icon name="messages" class="w-6 h-6" />
                        </span>
                        <h3 class="font-display text-lg font-semibold text-white">Email support</h3>
                    </div>
                    <p class="text-sm text-text-secondary mb-5 leading-relaxed">
                        General support and non-urgent inquiries about orders, funding, and account access.
                    </p>
                    @if($email !== '')
                        <a href="mailto:{{ $email }}" class="block text-accent font-semibold hover:underline break-all">{{ $email }}</a>
                    @endif
                    @if($emailAlt !== '')
                        <a href="mailto:{{ $emailAlt }}" class="block text-accent/90 font-medium hover:underline break-all mt-1">{{ $emailAlt }}</a>
                    @endif
                    @if($email === '' && $emailAlt === '')
                        <p class="text-sm text-text-muted">Email will appear once set in Admin → Settings.</p>
                    @else
                        <p class="mt-3 text-xs text-text-muted inline-flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-success shrink-0"></span>
                            Typical response within a few hours
                        </p>
                    @endif
                </div>

                {{-- Live chat --}}
                <div id="live-chat" class="p-6 sm:p-8 rounded-xl border border-border-subtle bg-elevated hover:bg-muted/40 transition-colors border-l-4 border-l-accent scroll-mt-28">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-accent">
                            <x-ui.icon name="chat" class="w-6 h-6" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-display text-lg font-semibold text-white">Live chat</h3>
                            <p class="text-xs text-text-muted inline-flex items-center gap-1.5 mt-0.5">
                                <span class="w-2 h-2 rounded-full {{ $chatOn ? 'bg-success animate-pulse' : 'bg-text-muted' }}"></span>
                                {{ $chatOn ? 'Agents available' : 'Currently offline' }}
                            </p>
                        </div>
                    </div>
                    <p class="text-sm text-text-secondary mb-5 leading-relaxed">
                        Instant messaging with our support team for time-sensitive wallet, exchange, and order questions.
                    </p>
                    @if($chatOn)
                        <p class="text-sm text-text-secondary mb-4">
                            Use the chat bubble in the bottom-right corner to start a conversation.
                        </p>
                        <button
                            type="button"
                            onclick="document.dispatchEvent(new CustomEvent('open-live-chat'))"
                            class="w-full py-3 rounded-xl bg-primary/15 border border-accent/30 text-accent font-semibold hover:bg-primary hover:text-white hover:border-primary transition-colors"
                        >
                            Open live chat
                        </button>
                        <p class="mt-3 text-xs text-text-muted text-center">Widget loads on this page only</p>
                    @else
                        <div class="rounded-lg border border-dashed border-border-default bg-muted/30 p-4 text-center mb-4">
                            <p class="text-sm text-text-secondary">Live chat is not enabled yet. Use email, phone, or open a support ticket.</p>
                        </div>
                        <a
                            href="{{ $supportHref }}"
                            class="block w-full text-center py-3 rounded-xl bg-muted border border-border-subtle text-white font-semibold hover:border-accent/40 transition-colors"
                        >
                            Open a support ticket
                        </a>
                    @endif
                </div>

                {{-- Phone --}}
                <div class="p-6 sm:p-8 rounded-xl border border-border-subtle bg-elevated hover:bg-muted/40 transition-colors">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-accent">
                            <x-ui.icon name="support" class="w-6 h-6" />
                        </span>
                        <h3 class="font-display text-lg font-semibold text-white">Phone support</h3>
                    </div>
                    <p class="text-sm text-text-secondary mb-5 leading-relaxed">
                        Voice assistance for urgent account or payment issues when you need to speak with someone.
                    </p>
                    @if($phone !== '')
                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="text-lg font-semibold text-white hover:text-accent transition-colors">
                            {{ $phone }}
                        </a>
                        <p class="mt-2 text-xs text-text-muted italic">Available during published support hours</p>
                    @else
                        <p class="text-sm text-text-muted">Phone number will appear once set in Admin → Settings.</p>
                    @endif
                </div>

                {{-- Tickets / community path --}}
                <div class="p-6 sm:p-8 rounded-xl border border-border-subtle bg-elevated hover:bg-muted/40 transition-colors">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-accent">
                            <x-ui.icon name="users" class="w-6 h-6" />
                        </span>
                        <h3 class="font-display text-lg font-semibold text-white">Support tickets</h3>
                    </div>
                    <p class="text-sm text-text-secondary mb-5 leading-relaxed">
                        Track conversations with our team from your dashboard — best for KYC, disputes, and follow-ups.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a
                            href="{{ $supportHref }}"
                            class="flex-1 text-center py-2.5 rounded-lg bg-white/5 border border-border-subtle text-sm font-semibold text-white hover:bg-white/10 hover:border-accent/30 transition-colors"
                        >
                            New ticket
                        </a>
                        <a
                            href="{{ $ticketsHref }}"
                            class="flex-1 text-center py-2.5 rounded-lg bg-white/5 border border-border-subtle text-sm font-semibold text-white hover:bg-white/10 hover:border-accent/30 transition-colors"
                        >
                            My tickets
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick links sidebar --}}
        <aside class="lg:col-span-4">
            <div class="lg:sticky lg:top-28 space-y-5">
                <div class="glassmorphism rounded-xl p-6 sm:p-7">
                    <h3 class="font-display text-lg font-semibold text-white mb-5 flex items-center gap-2">
                        <span class="text-accent"><x-ui.icon name="bookmark" class="w-5 h-5" /></span>
                        Quick links
                    </h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('help') }}" class="flex items-center justify-between gap-3 p-3 rounded-lg hover:bg-white/5 group transition-colors">
                                <span class="text-sm text-text-secondary group-hover:text-white transition-colors">Help Center</span>
                                <span class="text-text-muted group-hover:text-accent"><x-ui.icon name="arrow-right" class="w-4 h-4" /></span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('help.article', 'billing-wallets-payments') }}" class="flex items-center justify-between gap-3 p-3 rounded-lg hover:bg-white/5 group transition-colors">
                                <span class="text-sm text-text-secondary group-hover:text-white transition-colors">Billing &amp; payments</span>
                                <span class="text-text-muted group-hover:text-accent"><x-ui.icon name="arrow-right" class="w-4 h-4" /></span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('help.article', 'keeping-account-secure') }}" class="flex items-center justify-between gap-3 p-3 rounded-lg hover:bg-white/5 group transition-colors">
                                <span class="text-sm text-text-secondary group-hover:text-white transition-colors">Security &amp; KYC</span>
                                <span class="text-text-muted group-hover:text-accent"><x-ui.icon name="arrow-right" class="w-4 h-4" /></span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('exchange') }}" class="flex items-center justify-between gap-3 p-3 rounded-lg hover:bg-white/5 group transition-colors">
                                <span class="text-sm text-text-secondary group-hover:text-white transition-colors">Exchange rates</span>
                                <span class="text-text-muted group-hover:text-accent"><x-ui.icon name="arrow-right" class="w-4 h-4" /></span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('legal') }}" class="flex items-center justify-between gap-3 p-3 rounded-lg hover:bg-white/5 group transition-colors">
                                <span class="text-sm text-text-secondary group-hover:text-white transition-colors">Legal hub</span>
                                <span class="text-text-muted group-hover:text-accent"><x-ui.icon name="arrow-right" class="w-4 h-4" /></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="rounded-xl p-6 sm:p-7 bg-gradient-to-br from-elevated to-muted/50 border border-border-subtle">
                    <h3 class="font-display text-lg font-semibold text-white mb-2">Prefer self-serve?</h3>
                    <p class="text-sm text-text-secondary mb-5 leading-relaxed">
                        Most wallet, exchange, and marketplace questions are answered in our Help Center guides.
                    </p>
                    <x-ui.button href="{{ route('help') }}" variant="primary" size="md" class="w-full justify-center">
                        Go to Help Center
                    </x-ui.button>
                </div>
            </div>
        </aside>
    </div>
</section>

@if($chatOn)
    @include('partials.marketing.live-chat-widget')
    <script>
        document.addEventListener('open-live-chat', function () {
            var el = document.getElementById('live-chat');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            // Smartsupp / Jivo expose their own open APIs when ready
            try {
                if (typeof window.smartsupp === 'function') {
                    window.smartsupp('chat:open');
                }
            } catch (e) {}
            try {
                if (window.jivo_api && typeof window.jivo_api.open === 'function') {
                    window.jivo_api.open();
                }
            } catch (e) {}
        });
    </script>
@endif
@endsection
