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
    $sections = $document['sections'] ?? [];
    $ticketHref = auth()->check()
        ? route('dashboard.support.create')
        : route('login');
@endphp

@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Legal'],
        ['label' => $document['label'] ?? 'Documents'],
    ],
    'title' => $document['label'] ?? 'Legal',
    'subtitle' => $document['intro'] ?? 'Terms of Service and Privacy Policy for 7th Trade Hub.',
])

<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-14 sm:pb-20">
    {{-- Document switcher --}}
    <div class="flex flex-wrap gap-2 mb-8 sm:mb-10" role="tablist" aria-label="Legal documents">
        @foreach($documents as $key => $doc)
            <a
                href="{{ route('legal', ['doc' => $key]) }}"
                role="tab"
                aria-selected="{{ $activeKey === $key ? 'true' : 'false' }}"
                @class([
                    'px-4 sm:px-5 py-2.5 rounded-xl text-sm font-semibold border transition-colors',
                    'bg-primary border-primary text-white' => $activeKey === $key,
                    'bg-elevated border-border-default text-text-secondary hover:text-white hover:border-accent/40' => $activeKey !== $key,
                ])
            >
                {{ $doc['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Explicit breakpoints: built Tailwind may omit lg:block / lg:grid, which left desktop empty --}}
    <style>
        .legal-mobile { display: block; }
        .legal-desktop { display: none; }
        @media (min-width: 1024px) {
            .legal-mobile { display: none; }
            .legal-desktop { display: block; }
        }
    </style>

    @if(empty($sections))
        <x-ui.empty
            icon="info"
            title="No legal content"
            description="Legal documents have not been configured yet."
        />
    @else
        {{-- Mobile: accordion --}}
        <div class="legal-mobile space-y-3 mb-8">
            <div class="rounded-xl p-4 bg-muted/50 border border-border-subtle border-l-4 border-l-accent mb-4">
                <h2 class="font-display text-base font-semibold text-white mb-1">{{ $document['label'] }} summary</h2>
                <p class="text-sm text-text-secondary leading-relaxed">{{ $document['summary'] ?? '' }}</p>
            </div>
            @foreach($sections as $section)
                <details class="group glassmorphism rounded-xl overflow-hidden [&_summary::-webkit-details-marker]:hidden" @if($loop->first) open @endif>
                    <summary class="flex justify-between items-center gap-3 p-4 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-semibold text-sm text-white text-left flex items-center gap-2">
                            @if(! empty($section['number']))
                                <span class="text-[10px] font-bold bg-primary/15 text-accent px-1.5 py-0.5 rounded">{{ $section['number'] }}</span>
                            @endif
                            {{ $section['title'] }}
                        </span>
                        <span class="text-text-secondary transition-transform group-open:rotate-180 shrink-0">
                            <x-ui.icon name="chevron-down" class="w-5 h-5" />
                        </span>
                    </summary>
                    <div class="px-4 pb-4 border-t border-border-subtle pt-3">
                        @include('partials.legal.section-body', ['section' => $section, 'legalEmail' => $legalEmail, 'ticketHref' => $ticketHref])
                    </div>
                </details>
            @endforeach
        </div>

        {{-- Desktop: sticky nav + full document --}}
        <div class="legal-desktop">
            <div class="flex gap-8 items-start">
                <aside class="w-64 shrink-0 sticky top-28 self-start">
                    <div class="glassmorphism rounded-xl p-4">
                        <h3 class="text-[11px] font-medium uppercase tracking-wider text-text-secondary mb-3 px-2">
                            Document sections
                        </h3>
                        <nav class="flex flex-col gap-1" aria-label="Document sections">
                            @foreach($sections as $section)
                                <a
                                    href="#{{ $section['id'] }}"
                                    class="px-3 py-2.5 rounded-lg text-sm font-medium text-text-secondary hover:bg-muted hover:text-accent transition-colors"
                                >
                                    {{ $section['nav'] }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>

                <div class="min-w-0 flex-1 max-w-[800px] space-y-8">
                    <div class="rounded-xl p-6 bg-muted/50 border border-border-subtle border-l-4 border-l-accent">
                        <div class="flex items-start gap-4">
                            <span class="text-accent shrink-0 mt-0.5"><x-ui.icon name="info" class="w-7 h-7" /></span>
                            <div>
                                <h2 class="font-display text-lg font-semibold text-white mb-2">Legal summary</h2>
                                <p class="text-sm text-text-secondary leading-relaxed">{{ $document['summary'] ?? '' }}</p>
                                @if($updatedAt)
                                    <p class="text-xs text-text-muted mt-3">Last updated {{ \Illuminate\Support\Carbon::parse($updatedAt)->format('F Y') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @foreach($sections as $section)
                        <section id="{{ $section['id'] }}" class="scroll-mt-28">
                            <h2 class="font-display text-xl sm:text-2xl font-semibold text-accent mb-4 flex items-center gap-3 flex-wrap">
                                @if(! empty($section['number']))
                                    <span class="text-xs font-bold bg-primary/15 text-accent px-2 py-1 rounded">{{ $section['number'] }}</span>
                                @endif
                                {{ $section['title'] }}
                            </h2>
                            @include('partials.legal.section-body', ['section' => $section, 'legalEmail' => $legalEmail, 'ticketHref' => $ticketHref])
                        </section>

                        @if(! $loop->last)
                            <hr class="border-border-subtle">
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
