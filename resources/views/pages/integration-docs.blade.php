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
    <div class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <nav class="rounded-2xl border border-border-default bg-elevated/60 p-4 text-sm" aria-label="Integration docs">
                @foreach ($groups as $group => $items)
                    <p class="mb-2 mt-4 first:mt-0 text-xs font-semibold uppercase tracking-wide text-text-muted">{{ $group }}</p>
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
            </nav>
        </aside>

        <article class="min-w-0 rounded-2xl border border-border-default bg-elevated/40 p-6 sm:p-8">
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
                <div class="integration-docs prose prose-sm sm:prose-base max-w-none prose-headings:text-text-primary prose-p:text-text-secondary prose-a:text-primary prose-code:text-text-primary prose-pre:bg-muted/60">
                    {!! $document['html'] !!}
                </div>
            @else
                <pre class="overflow-x-auto rounded-xl bg-muted/60 p-4 text-xs sm:text-sm text-text-primary"><code>{{ $document['content'] }}</code></pre>
            @endif
        </article>
    </div>
</section>
@endsection
