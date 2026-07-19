@extends('layouts.marketing')

@section('title', 'Help Center | 7th Trade Hub')

@section('content')
@php
    $resolvedCategories = collect($categories)->map(function (array $cat) {
        $needsAuth = ! empty($cat['auth']);
        if ($needsAuth && ! auth()->check()) {
            $href = route($cat['guest_href'] ?? 'login');
        } else {
            $href = route($cat['href']);
        }

        return array_merge($cat, ['resolved_href' => $href]);
    })->values();

    $ticketHref = auth()->check()
        ? route('dashboard.support.create')
        : route('login');
    $supportHref = auth()->check()
        ? route('dashboard.support.index')
        : route('login');

    $toneBg = [
        'primary' => 'bg-primary/10 text-accent',
        'accent' => 'bg-accent/10 text-accent',
        'warning' => 'bg-warning/10 text-warning',
        'success' => 'bg-success/10 text-success',
        'info' => 'bg-sky-500/10 text-sky-400',
    ];
@endphp

<div
    x-data="{
        q: '',
        categoryTexts: {{ Js::from($resolvedCategories->map(fn ($c) => $c['title'].' '.$c['description'])->values()) }},
        faqTexts: {{ Js::from(collect($faqs)->map(fn ($f) => ($f['q'] ?? '').' '.($f['a'] ?? ''))->values()) }},
        matches(text) {
            const term = this.q.trim().toLowerCase();
            if (!term) return true;
            return String(text || '').toLowerCase().includes(term);
        },
        get hasCategoryMatches() {
            return this.categoryTexts.some((t) => this.matches(t));
        },
        get hasFaqMatches() {
            return this.faqTexts.some((t) => this.matches(t));
        },
        init() {
            window.addEventListener('keydown', (e) => {
                if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                    e.preventDefault();
                    this.$refs.search?.focus();
                }
            });
        }
    }"
>

{{-- Hero --}}
<section class="relative px-5 sm:px-6 max-w-marketing mx-auto text-center pt-10 sm:pt-14 pb-12 sm:pb-16">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(11,106,57,0.12)_0%,transparent_70%)]" aria-hidden="true"></div>
    <div class="relative z-10">
        <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white mb-4 leading-tight">
            How can we help you?
        </h1>
        <p class="text-sm sm:text-base lg:text-lg text-text-secondary mb-8 max-w-2xl mx-auto leading-relaxed">
            Search guides for wallet, exchange, services, and marketplace — or open a support ticket when you need a human.
        </p>
        <div class="max-w-2xl mx-auto relative">
            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-accent pointer-events-none">
                <x-ui.icon name="search" class="w-5 h-5" />
            </span>
            <label for="help-search" class="sr-only">Search help center</label>
            <input
                id="help-search"
                x-ref="search"
                x-model="q"
                type="search"
                placeholder="Search our knowledge base..."
                class="w-full pl-14 pr-16 py-4 sm:py-5 bg-elevated rounded-full border border-border-default focus:border-accent focus:ring-1 focus:ring-accent/40 text-sm sm:text-base text-text-primary placeholder:text-text-muted transition-all shadow-xl"
            >
            <kbd class="absolute right-5 top-1/2 -translate-y-1/2 px-2 py-1 bg-muted rounded text-[10px] font-medium text-text-muted border border-border-subtle hidden sm:inline-block">/</kbd>
        </div>
    </div>
</section>

{{-- Categories --}}
<section class="px-5 sm:px-6 max-w-marketing mx-auto pb-14 sm:pb-16">
    <div class="mb-6 sm:mb-8">
        <h2 class="font-display text-xl sm:text-2xl font-semibold text-accent mb-1">Browse categories</h2>
        <p class="text-sm text-text-secondary">Common topics across the platform</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        @foreach($resolvedCategories as $cat)
            <a
                href="{{ $cat['resolved_href'] }}"
                x-show="matches(@js($cat['title'].' '.$cat['description']))"
                class="group glassmorphism p-6 sm:p-7 rounded-xl flex flex-col items-start hover:border-accent/40 transition-all duration-300 hover:-translate-y-1"
            >
                <div class="w-14 h-14 rounded-full flex items-center justify-center mb-4 {{ $toneBg[$cat['tone'] ?? 'primary'] ?? $toneBg['primary'] }}">
                    <x-ui.icon :name="$cat['icon']" class="w-6 h-6" />
                </div>
                <h3 class="font-display text-lg font-semibold text-white mb-2">{{ $cat['title'] }}</h3>
                <p class="text-sm text-text-secondary leading-relaxed mb-5 flex-1">{{ $cat['description'] }}</p>
                <span class="mt-auto text-xs font-bold uppercase tracking-wider text-accent inline-flex items-center gap-2 group-hover:gap-3 transition-all">
                    {{ $cat['cta'] }}
                    <x-ui.icon name="arrow-right" class="w-4 h-4" />
                </span>
            </a>
        @endforeach
    </div>
    <p x-show="!hasCategoryMatches" x-cloak class="text-sm text-text-secondary text-center py-8">
        No categories match your search.
    </p>
</section>

{{-- FAQs --}}
@if(count($faqs))
<section id="faqs" class="bg-elevated/40 border-y border-border-subtle py-14 sm:py-16">
    <div class="px-5 sm:px-6 max-w-3xl mx-auto">
        <h2 class="font-display text-xl sm:text-2xl font-semibold text-white text-center mb-8">
            Frequently asked questions
        </h2>
        <div class="space-y-3">
            @foreach($faqs as $faq)
                <details
                    x-show="matches(@js(($faq['q'] ?? '').' '.($faq['a'] ?? '')))"
                    class="group glassmorphism rounded-xl overflow-hidden [&_summary::-webkit-details-marker]:hidden"
                    @if($loop->first) open @endif
                >
                    <summary class="flex justify-between items-center gap-4 p-4 sm:p-5 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-semibold text-sm sm:text-base text-white text-left">{{ $faq['q'] ?? '' }}</span>
                        <span class="text-text-secondary transition-transform group-open:rotate-180 shrink-0">
                            <x-ui.icon name="chevron-down" class="w-5 h-5" />
                        </span>
                    </summary>
                    <div class="px-4 sm:px-5 pb-4 sm:pb-5 text-sm text-text-secondary leading-relaxed border-t border-border-subtle pt-3">
                        {{ $faq['a'] ?? '' }}
                    </div>
                </details>
            @endforeach
        </div>
        <p x-show="!hasFaqMatches" x-cloak class="text-sm text-text-secondary text-center py-6">
            No FAQs match your search.
        </p>
    </div>
</section>
@endif

{{-- Still need help --}}
<section class="px-5 sm:px-6 max-w-marketing mx-auto py-14 sm:py-16">
    <div class="rounded-2xl sm:rounded-3xl bg-gradient-to-br from-elevated to-muted/40 border border-border-subtle p-8 sm:p-12 text-center overflow-hidden relative">
        <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-primary/20 rounded-full blur-[100px] pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -top-20 -left-20 w-64 h-64 bg-accent/10 rounded-full blur-[100px] pointer-events-none" aria-hidden="true"></div>
        <div class="relative z-10 max-w-3xl mx-auto">
            <h2 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4">Still need help?</h2>
            <p class="text-sm sm:text-base text-text-secondary mb-8 leading-relaxed">
                If you can’t find what you’re looking for, open a ticket from your dashboard and our team will follow up.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
                <a href="{{ $ticketHref }}" class="flex flex-col items-center gap-3 p-5 sm:p-6 bg-surface/60 rounded-xl border border-border-subtle hover:border-accent/40 hover:bg-muted/40 transition-colors">
                    <span class="text-accent"><x-ui.icon name="support" class="w-8 h-8" /></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-white">Open a ticket</span>
                </a>
                <a href="{{ $supportHref }}" class="flex flex-col items-center gap-3 p-5 sm:p-6 bg-surface/60 rounded-xl border border-border-subtle hover:border-accent/40 hover:bg-muted/40 transition-colors">
                    <span class="text-accent"><x-ui.icon name="chat" class="w-8 h-8" /></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-white">My tickets</span>
                </a>
                <a href="{{ route('exchange') }}" class="flex flex-col items-center gap-3 p-5 sm:p-6 bg-surface/60 rounded-xl border border-border-subtle hover:border-accent/40 hover:bg-muted/40 transition-colors">
                    <span class="text-accent"><x-ui.icon name="swap" class="w-8 h-8" /></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-white">Exchange rates</span>
                </a>
            </div>
        </div>
    </div>
</section>

</div>
@endsection
