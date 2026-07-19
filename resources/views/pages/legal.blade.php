@extends('layouts.marketing')

@section('title', ($document['label'] ?? 'Legal').' | 7th Trade Hub')

@section('content')
@php
    $documents = config('legal.documents', []);
    $updatedAt = config('legal.updated_at');
    $legalEmail = config('legal.contact.email');
    $activeKey = $activeDoc ?? 'terms';
    if (! isset($documents[$activeKey])) {
        $activeKey = array_key_first($documents) ?: 'terms';
    }
    $document = $documents[$activeKey] ?? [];
    $ticketHref = auth()->check()
        ? route('dashboard.support.create')
        : route('login');
    $sideImage = asset('assets/images/Image_ro410gro410gro41.png');
@endphp

{{-- Hero --}}
<section class="relative overflow-hidden px-5 sm:px-6 pb-10 sm:pb-12 pt-10 sm:pt-14">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(11,106,57,0.12)_0%,transparent_70%)]" aria-hidden="true"></div>
    <div class="max-w-marketing mx-auto relative z-10 text-center">
        <span class="text-[11px] font-medium uppercase tracking-widest text-accent mb-3 block">Compliance &amp; Legal</span>
        <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white mb-4 leading-tight">
            Legal
        </h1>
        <p class="text-sm sm:text-base text-text-secondary max-w-2xl mx-auto leading-relaxed mb-8">
            Terms of Service and Privacy Policy for 7th Trade Hub — wallet, marketplace, services, and exchange.
        </p>

        <div class="inline-flex p-1 rounded-xl bg-elevated border border-border-default gap-1" role="tablist" aria-label="Legal documents">
            @foreach($documents as $key => $doc)
                <a
                    href="{{ route('legal', ['doc' => $key]) }}"
                    role="tab"
                    aria-selected="{{ $activeKey === $key ? 'true' : 'false' }}"
                    @class([
                        'px-4 sm:px-5 py-2 rounded-lg text-sm font-semibold transition-colors',
                        'bg-primary text-white' => $activeKey === $key,
                        'text-text-secondary hover:text-white' => $activeKey !== $key,
                    ])
                >
                    {{ $doc['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Content --}}
<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-14 sm:pb-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        <aside class="lg:col-span-3 lg:sticky lg:top-28">
            <div class="glassmorphism rounded-xl p-4 sm:p-5">
                <h2 class="text-[11px] font-medium uppercase tracking-wider text-text-secondary mb-3 px-2">
                    {{ $document['label'] ?? 'Document' }} sections
                </h2>
                <nav class="flex flex-col gap-0.5 max-h-[60vh] overflow-y-auto" aria-label="Document sections">
                    @foreach(($document['sections'] ?? []) as $section)
                        <a
                            href="#{{ $section['id'] }}"
                            class="px-3 py-2 rounded-lg text-sm text-text-secondary hover:bg-muted hover:text-accent transition-colors"
                        >
                            {{ $section['nav'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="lg:col-span-9 space-y-8">
            <div class="rounded-xl p-5 sm:p-6 bg-muted/50 border border-border-subtle border-l-4 border-l-accent">
                <div class="flex items-start gap-4">
                    <span class="text-accent shrink-0 mt-0.5"><x-ui.icon name="info" class="w-7 h-7" /></span>
                    <div>
                        <h2 class="font-display text-lg font-semibold text-white mb-2">{{ $document['label'] ?? 'Legal' }} summary</h2>
                        <p class="text-sm text-text-secondary leading-relaxed">{{ $document['summary'] ?? '' }}</p>
                        @if($updatedAt)
                            <p class="text-xs text-text-muted mt-3">Last updated {{ \Illuminate\Support\Carbon::parse($updatedAt)->format('F Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-10">
                @foreach(($document['sections'] ?? []) as $section)
                    <section id="{{ $section['id'] }}" class="scroll-mt-28">
                        <h2 class="font-display text-xl sm:text-2xl font-semibold text-accent mb-4 flex items-center gap-3 flex-wrap">
                            @if(! empty($section['number']))
                                <span class="text-xs font-bold bg-primary/15 text-accent px-2 py-1 rounded">{{ $section['number'] }}</span>
                            @endif
                            {{ $section['title'] }}
                        </h2>

                        <div @class([
                            'space-y-4 text-sm sm:text-base text-text-secondary leading-relaxed',
                            'rounded-xl border border-danger/25 bg-danger/5 p-4 sm:p-5' => ($section['variant'] ?? null) === 'danger',
                        ])>
                            @foreach(($section['paragraphs'] ?? []) as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach

                            @if(! empty($section['checklist']))
                                <ul class="list-none space-y-3">
                                    @foreach($section['checklist'] as $item)
                                        <li class="flex items-start gap-3">
                                            <span class="text-accent mt-0.5 shrink-0"><x-ui.icon name="check" class="w-5 h-5" /></span>
                                            <span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if(! empty($section['bullets']))
                                <ul class="list-disc ml-5 space-y-2">
                                    @foreach($section['bullets'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if(! empty($section['cards']))
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                                    @foreach($section['cards'] as $card)
                                        <div class="p-4 rounded-lg bg-elevated border border-border-subtle">
                                            <h3 class="text-accent font-bold text-sm mb-1">{{ $card['title'] }}</h3>
                                            <p class="text-sm text-text-secondary">{{ $card['body'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(! empty($section['blocks']))
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($section['blocks'] as $block)
                                        <div class="flex items-center gap-2 text-sm text-text-primary">
                                            <span class="text-danger shrink-0"><x-ui.icon name="warning" class="w-5 h-5" /></span>
                                            <span>{{ $block }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(($section['variant'] ?? null) === 'contact')
                                <div class="glassmorphism p-4 sm:p-5 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-2">
                                    <div class="space-y-3">
                                        @if($legalEmail)
                                            <div>
                                                <p class="text-[11px] font-medium uppercase tracking-wider text-text-secondary">Legal email</p>
                                                <a href="mailto:{{ $legalEmail }}" class="text-accent font-medium hover:underline">{{ $legalEmail }}</a>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-[11px] font-medium uppercase tracking-wider text-text-secondary">Support</p>
                                            <p class="text-sm text-text-primary">Open a ticket from your dashboard for legal or privacy questions.</p>
                                        </div>
                                    </div>
                                    <x-ui.button href="{{ $ticketHref }}" variant="primary" size="md" class="shrink-0 hover:!bg-accent">
                                        Open ticket
                                    </x-ui.button>
                                </div>
                            @endif
                        </div>
                    </section>

                    @if(($section['id'] ?? '') === 'security' && $activeKey === 'terms')
                        <div class="rounded-xl overflow-hidden border border-border-subtle relative h-40 sm:h-48">
                            <img src="{{ $sideImage }}" alt="" class="w-full h-full object-cover opacity-50">
                            <div class="absolute inset-0 bg-gradient-to-r from-surface via-surface/40 to-transparent"></div>
                        </div>
                    @endif

                    @if(! $loop->last && ($section['id'] ?? '') !== 'security')
                        <hr class="border-border-subtle">
                    @elseif(! $loop->last && ($section['id'] ?? '') === 'security')
                        {{-- image acts as divider --}}
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
