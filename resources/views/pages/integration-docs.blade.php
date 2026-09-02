@extends('layouts.marketing')

@section('title', ($document['title'] ?? 'Site Integration Docs'))

@section('content')
@php
    $groups = collect($navigation)->groupBy('group');
    $currentPath = $document['path'] ?? 'README';
@endphp

@include('partials.marketing.page-header', [
    'breadcrumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Developers', 'href' => route('developers.integrations.index')],
        ['label' => $document['title'] ?? 'Documentation'],
    ],
    'title' => 'Site Integration Documentation',
    'subtitle' => 'Protocol v1 — integrate independent websites with 7th Trade Hub.',
])

<section class="max-w-marketing mx-auto px-5 sm:px-6 pb-16 sm:pb-20">
    {{-- Explicit breakpoints: built Tailwind may omit lg:* utilities on shared hosting --}}
    <style>
        .integration-docs-layout {
            display: grid;
            gap: 2rem;
        }
        .integration-docs-sidebar {
            position: relative;
            z-index: 1;
        }
        .integration-docs-sidebar nav {
            border-radius: 1rem;
            border: 1px solid var(--color-border-default, rgba(255, 255, 255, 0.1));
            background: var(--color-elevated, #111827);
            padding: 1rem;
            font-size: 0.875rem;
        }
        .integration-docs-article {
            position: relative;
            z-index: 0;
            min-width: 0;
            border-radius: 1rem;
            border: 1px solid var(--color-border-default, rgba(255, 255, 255, 0.1));
            background: color-mix(in srgb, var(--color-elevated, #111827) 40%, transparent);
            padding: 1.5rem;
        }
        @media (min-width: 640px) {
            .integration-docs-article {
                padding: 2rem;
            }
        }
        @media (min-width: 1024px) {
            .integration-docs-layout {
                grid-template-columns: 16rem minmax(0, 1fr);
                align-items: start;
            }
            .integration-docs-sidebar {
                position: sticky;
                top: 7rem;
                align-self: start;
                max-height: calc(100vh - 8.5rem);
            }
            .integration-docs-sidebar nav {
                display: flex;
                flex-direction: column;
                max-height: calc(100vh - 8.5rem);
                overflow: hidden;
            }
            .integration-docs-sidebar .integration-docs-nav-scroll {
                flex: 1 1 auto;
                min-height: 0;
                overflow-y: auto;
                overscroll-behavior: contain;
                padding-right: 0.125rem;
            }
        }
        .integration-docs .prose pre {
            overflow-x: auto;
        }
        .integration-docs .prose table {
            display: block;
            overflow-x: auto;
        }
    </style>

    <div class="integration-docs-layout">
        <aside class="integration-docs-sidebar" aria-label="Integration docs navigation">
            <nav>
                <p class="mb-3 shrink-0 text-xs font-semibold uppercase tracking-wide text-text-muted px-1">
                    Documentation
                </p>
                <div class="integration-docs-nav-scroll">
                    @foreach ($groups as $group => $items)
                        <p class="mb-2 mt-4 first:mt-0 text-xs font-semibold uppercase tracking-wide text-text-muted px-2">{{ $group }}</p>
                        <ul class="space-y-1">
                            @foreach ($items as $item)
                                @php $active = ($item['path'] === $currentPath); @endphp
                                <li>
                                    <a
                                        href="{{ $item['path'] === 'README' ? route('developers.integrations.index') : route('developers.integrations.show', ['path' => $item['path']]) }}"
                                        @class([
                                            'block rounded-lg px-3 py-2 transition',
                                            'bg-primary/10 font-medium text-primary' => $active,
                                            'text-text-secondary hover:bg-muted hover:text-text-primary' => ! $active,
                                        ])
                                    >
                                        {{ $item['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </nav>
        </aside>

        <article class="integration-docs-article integration-docs">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-border-default pb-4">
                <h1 class="text-2xl font-semibold text-text-primary">{{ $document['title'] }}</h1>
                @if (($document['extension'] ?? '') !== 'md')
                    <a
                        href="{{ route('developers.integrations.download', ['path' => $document['path']]) }}"
                        class="text-sm font-medium text-primary hover:underline"
                    >
                        Download raw file
                    </a>
                @endif
            </div>

            @if (! empty($document['html']))
                <div class="prose prose-sm sm:prose-base max-w-none prose-headings:text-text-primary prose-p:text-text-secondary prose-a:text-primary prose-code:text-text-primary prose-pre:bg-muted/60">
                    {!! $document['html'] !!}
                </div>
            @else
                <pre class="overflow-x-auto rounded-xl bg-muted/60 p-4 text-xs sm:text-sm text-text-primary"><code>{{ $document['content'] }}</code></pre>
            @endif
        </article>
    </div>
</section>
@endsection
