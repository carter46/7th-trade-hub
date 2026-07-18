@extends('layouts.marketing')

@section('title', ($content['label'] ?? 'Services').' | 7th Trade Hub')

@section('content')
<section class="py-8 sm:py-12">
    <div class="max-w-marketing mx-auto px-5 sm:px-6 space-y-10">
        @include('partials.marketing.page-header', [
            'breadcrumbs' => [
                ['label' => 'Home', 'href' => route('home')],
                ['label' => 'Services', 'href' => route('services')],
                ['label' => $content['label']],
            ],
            'title' => $content['hero_title'] ?? $content['label'],
            'subtitle' => $content['hero_subtitle'] ?? $content['short_description'],
        ])

        @if(!empty($content['benefits']))
            <div>
                <h2 class="text-lg font-bold font-display mb-3">Why browse here</h2>
                <ul class="grid sm:grid-cols-2 gap-2 text-sm text-slate-300">
                    @foreach($content['benefits'] as $benefit)
                        <li class="flex gap-2"><span class="text-accent">•</span><span>{{ $benefit }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <h2 class="text-xl font-bold font-display mb-5">Choose a type</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($types as $card)
                    @include('partials.catalog.explore-card', ['card' => $card])
                @endforeach
            </div>
        </div>

        @if(!empty($content['faq']))
            <div>
                <h2 class="text-lg font-bold font-display mb-3">FAQ</h2>
                <dl class="space-y-4">
                    @foreach($content['faq'] as $item)
                        <div class="glassmorphism rounded-xl p-4">
                            <dt class="font-semibold mb-1">{{ $item['q'] ?? '' }}</dt>
                            <dd class="text-sm text-slate-400">{{ $item['a'] ?? '' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </div>
</section>
@endsection
