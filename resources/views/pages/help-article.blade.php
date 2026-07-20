@extends('layouts.marketing')

@section('title', ($article['title'] ?? 'Help').' | 7th Trade Hub')

@section('content')
@php
    $sections = $article['sections'] ?? [];
    $sectionCount = count($sections);
    $related = $article['related'] ?? [];
    $actions = $article['platform_actions'] ?? [];
@endphp

@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Help Center', 'href' => route('help')],
        ['label' => $article['title'] ?? 'Guide'],
    ],
    'title' => $article['title'] ?? 'Help guide',
    'subtitle' => $article['intro'] ?? '',
])

{{-- Single layout switch via matchMedia so section ids stay unique (deep links work) --}}
<div
    x-data="helpArticleProgress({{ $sectionCount }})"
    x-init="init()"
    class="relative"
>
    <div class="sticky top-16 z-30 h-1 bg-muted/80" aria-hidden="true">
        <div class="h-full bg-accent transition-all duration-150" :style="'width:' + percent + '%'"></div>
    </div>

    <section class="max-w-marketing mx-auto px-5 sm:px-6 pb-28 sm:pb-36 lg:pb-44">
        <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs sm:text-sm text-text-secondary mb-6 sm:mb-8">
            <span>{{ $article['reading_minutes'] ?? 1 }} min read</span>
            <span class="text-border-default" aria-hidden="true">·</span>
            <span>Updated {{ $article['updated_at_display'] ?? ($article['updated_at'] ?? '') }}</span>
            <span class="text-border-default" aria-hidden="true">·</span>
            <span x-text="'Step ' + currentStep + ' of ' + totalSteps"></span>
            <span class="text-border-default" aria-hidden="true">·</span>
            <span x-text="percent + '%'"></span>
            @if(! empty($article['printable']))
                <button
                    type="button"
                    onclick="window.print()"
                    class="ml-auto text-accent hover:underline font-medium"
                >
                    Print
                </button>
            @endif
            <span class="hidden" data-help-pdf-slot aria-hidden="true"></span>
        </div>

        <style>
            .help-toc {
                display: none;
            }
            @media (min-width: 1024px) {
                .help-toc {
                    display: block;
                    position: sticky;
                    top: 7.5rem;
                    align-self: flex-start;
                    width: 16rem;
                    flex-shrink: 0;
                    max-height: calc(100vh - 9rem);
                }
                .help-toc-panel {
                    display: flex;
                    flex-direction: column;
                    min-height: calc(100vh - 9rem);
                    max-height: calc(100vh - 9rem);
                }
                .help-toc-nav {
                    flex: 1 1 auto;
                    overflow-y: auto;
                    min-height: 0;
                }
                .help-accordion-summary {
                    display: none !important;
                }
                .help-section-panel {
                    display: block !important;
                    border-top: 0 !important;
                    padding-top: 0 !important;
                }
                .help-section-card {
                    background: transparent;
                    border: 0;
                    border-radius: 0;
                    overflow: visible;
                }
                .help-section-card + .help-section-rule {
                    display: block;
                }
            }
            @media (max-width: 1023px) {
                .help-section-rule {
                    display: none;
                }
                .help-desktop-heading {
                    display: none !important;
                }
            }
            @media print {
                .help-toc, .sticky, [onclick="window.print()"] { display: none !important; }
                .help-accordion-summary { display: none !important; }
                .help-section-panel { display: block !important; }
            }
        </style>

        @if(empty($sections))
            <x-ui.empty icon="info" title="Empty guide" description="This article has no sections yet." />
        @else
            <div class="flex gap-8 items-start">
                <aside class="help-toc">
                    <div class="help-toc-panel glassmorphism rounded-xl p-4">
                        <h3 class="text-[11px] font-medium uppercase tracking-wider text-text-secondary mb-3 px-2 shrink-0">
                            In this guide
                        </h3>
                        <p class="text-xs text-text-muted px-2 mb-3 shrink-0" x-text="'Step ' + currentStep + ' of ' + totalSteps + ' · ' + percent + '%'"></p>
                        <nav class="help-toc-nav flex flex-col gap-1.5" aria-label="Guide sections">
                            @foreach($sections as $i => $section)
                                <a
                                    href="#{{ $section['id'] }}"
                                    class="px-3 py-3 rounded-lg text-sm font-medium transition-colors"
                                    :class="currentStep === {{ $i + 1 }} ? 'bg-muted text-accent' : 'text-text-secondary hover:bg-muted hover:text-accent'"
                                >
                                    {{ $section['nav'] ?? $section['title'] }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>

                <div class="min-w-0 flex-1 max-w-[800px] space-y-3 lg:space-y-8 pb-8">
                    <div class="rounded-xl p-4 lg:p-6 bg-muted/50 border border-border-subtle border-l-4 border-l-accent">
                        <h2 class="help-desktop-heading font-display text-lg font-semibold text-white mb-2">Overview</h2>
                        <p class="text-sm text-text-secondary leading-relaxed">{{ $article['summary'] ?? '' }}</p>
                    </div>

                    @foreach($sections as $section)
                        <details
                            id="{{ $section['id'] }}"
                            data-help-section
                            class="help-section-card group glassmorphism rounded-xl overflow-hidden lg:overflow-visible scroll-mt-28 [&_summary::-webkit-details-marker]:hidden"
                            @if($loop->first) open @endif
                        >
                            <summary class="help-accordion-summary flex justify-between items-center gap-3 p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <span class="font-semibold text-sm text-white text-left">{{ $section['title'] }}</span>
                                <span class="text-text-secondary transition-transform group-open:rotate-180 shrink-0">
                                    <x-ui.icon name="chevron-down" class="w-5 h-5" />
                                </span>
                            </summary>
                            <div class="help-section-panel px-4 pb-4 lg:px-0 lg:pb-0 border-t border-border-subtle pt-3 lg:border-0">
                                <h2 class="help-desktop-heading font-display text-xl sm:text-2xl font-semibold text-accent mb-4">
                                    {{ $section['title'] }}
                                </h2>
                                @include('partials.help.section-body', ['section' => $section])
                            </div>
                        </details>
                        @if(! $loop->last)
                            <hr class="help-section-rule border-border-subtle hidden lg:block">
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if(count($actions))
            <div class="mt-12 flex flex-wrap gap-3">
                @foreach($actions as $action)
                    @php
                        $routeName = $action['route'] ?? null;
                        $needsAuth = ! empty($action['auth']);
                        $href = ($needsAuth && ! auth()->check())
                            ? route('login')
                            : (\Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : route('help'));
                    @endphp
                    <x-ui.button href="{{ $href }}" variant="{{ $loop->first ? 'primary' : 'secondary' }}" size="md">
                        {{ $action['label'] ?? 'Open' }}
                    </x-ui.button>
                @endforeach
            </div>
        @endif

        @if(count($related))
            <div class="mt-10 pt-8 border-t border-border-subtle">
                <h3 class="font-display text-lg font-semibold text-white mb-4">Related guides</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($related as $relatedSlug)
                        @php $relatedArticle = \App\Support\HelpContent::find($relatedSlug); @endphp
                        @if($relatedArticle)
                            <a
                                href="{{ route('help.article', $relatedSlug) }}"
                                class="px-4 py-2 rounded-xl text-sm font-medium bg-elevated border border-border-default text-text-secondary hover:text-accent hover:border-accent/40 transition-colors"
                            >
                                {{ $relatedArticle['title'] }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </section>
</div>

<script>
function helpArticleProgress(total) {
    return {
        totalSteps: Math.max(1, total || 1),
        currentStep: 1,
        percent: 0,
        init() {
            const sections = Array.from(document.querySelectorAll('[data-help-section]'));
            const desktopMq = window.matchMedia('(min-width: 1024px)');

            const syncDesktopOpen = () => {
                if (!desktopMq.matches) return;
                sections.forEach((el) => {
                    if (el.tagName === 'DETAILS') el.open = true;
                });
            };
            syncDesktopOpen();
            desktopMq.addEventListener('change', syncDesktopOpen);
            sections.forEach((el) => {
                if (el.tagName !== 'DETAILS') return;
                el.addEventListener('toggle', () => {
                    if (desktopMq.matches && !el.open) el.open = true;
                });
            });

            const updateScroll = () => {
                const doc = document.documentElement;
                const scrollable = doc.scrollHeight - window.innerHeight;
                this.percent = scrollable > 0
                    ? Math.min(100, Math.round((window.scrollY / scrollable) * 100))
                    : 0;
            };
            window.addEventListener('scroll', updateScroll, { passive: true });
            updateScroll();

            if (sections.length && 'IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        const idx = sections.indexOf(entry.target);
                        if (idx >= 0) this.currentStep = idx + 1;
                    });
                }, { rootMargin: '-40% 0px -45% 0px', threshold: 0 });
                sections.forEach((el) => observer.observe(el));
            }

            const openHash = () => {
                const id = decodeURIComponent((window.location.hash || '').replace(/^#/, ''));
                if (!id) return;
                const el = document.getElementById(id);
                if (!el) return;
                if (el.tagName === 'DETAILS') {
                    el.open = true;
                    if (!desktopMq.matches) {
                        sections.forEach((s) => {
                            if (s !== el && s.tagName === 'DETAILS') s.open = false;
                        });
                    }
                }
                const idx = sections.indexOf(el);
                if (idx >= 0) this.currentStep = idx + 1;
                requestAnimationFrame(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }));
            };
            openHash();
            window.addEventListener('hashchange', openHash);
        },
    };
}
</script>
@endsection
