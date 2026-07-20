@extends('layouts.marketing')

@section('title', 'Contact Us | 7th Trade Hub')

@section('content')
@php
    $phone = trim((string) ($contactPhone ?? ''));
    $email = trim((string) ($contactEmail ?? ''));
    $emailAlt = trim((string) ($contactEmailAlt ?? ''));
    $provider = strtolower(trim((string) ($liveChatProvider ?? 'none')));
    $chatOn = (bool) ($chatEnabled ?? false);
@endphp

@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Contact'],
    ],
    'title' => 'Contact Us',
    'subtitle' => 'Reach 7th Trade Hub support by live chat, phone, or email — or browse the Help Center.',
])

<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-28 sm:pb-36">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-start">
        {{-- Left: contact details --}}
        <div class="glassmorphism rounded-2xl p-6 sm:p-8 space-y-6">
            <div>
                <h2 class="font-display text-xl font-semibold text-white mb-2">Get in touch</h2>
                <p class="text-sm text-text-secondary leading-relaxed">
                    Our team can help with wallet funding, crypto sells, marketplace orders, KYC, and account questions.
                </p>
            </div>

            @if($phone !== '')
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wider text-text-secondary mb-1">Phone</p>
                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="text-accent font-medium hover:underline text-lg">{{ $phone }}</a>
                </div>
            @endif

            <div class="space-y-3">
                <p class="text-[11px] font-medium uppercase tracking-wider text-text-secondary">Email</p>
                @if($email !== '')
                    <a href="mailto:{{ $email }}" class="block text-accent font-medium hover:underline">{{ $email }}</a>
                @endif
                @if($emailAlt !== '')
                    <a href="mailto:{{ $emailAlt }}" class="block text-accent font-medium hover:underline">{{ $emailAlt }}</a>
                @endif
                @if($email === '' && $emailAlt === '')
                    <p class="text-sm text-text-muted">Email details will appear once configured in Admin → Settings.</p>
                @endif
            </div>

            <div class="pt-2">
                <x-ui.button href="{{ route('help') }}" variant="primary" size="md">
                    Back to Help Center
                </x-ui.button>
            </div>
        </div>

        {{-- Right: live chat panel --}}
        <div class="glassmorphism rounded-2xl p-6 sm:p-8 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full {{ $chatOn ? 'bg-success animate-pulse' : 'bg-text-muted' }}"></span>
                <h2 class="font-display text-xl font-semibold text-white">
                    {{ $chatOn ? 'Live chat online' : 'Live chat' }}
                </h2>
            </div>
            @if($chatOn)
                <p class="text-sm text-text-secondary leading-relaxed">
                    Support agents are available via the chat bubble. Use the widget in the bottom-right corner to start a conversation.
                </p>
                <div class="rounded-xl border border-border-subtle bg-muted/40 p-6 text-center">
                    <span class="inline-flex text-accent mb-2"><x-ui.icon name="chat" class="w-8 h-8" /></span>
                    <p class="text-sm font-semibold text-white">Chat widget loaded</p>
                    <p class="text-xs text-text-secondary mt-1">Look for the chat icon at the bottom right of this page.</p>
                </div>
            @else
                <p class="text-sm text-text-secondary leading-relaxed">
                    Live chat is not enabled yet. Use phone or email on the left, or open a ticket from your dashboard after signing in.
                </p>
                <div class="rounded-xl border border-dashed border-border-default bg-muted/30 p-6 text-center">
                    <span class="inline-flex text-text-muted mb-2"><x-ui.icon name="chat" class="w-8 h-8" /></span>
                    <p class="text-sm text-text-secondary">Configure Smartsupp or Jivo in Admin → Settings to enable live chat.</p>
                </div>
            @endif
        </div>
    </div>
</section>

@if($chatOn)
    @include('partials.marketing.live-chat-widget')
@endif
@endsection
