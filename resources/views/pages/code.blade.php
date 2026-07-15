@extends('layouts.marketing')

@section('title', 'Code & API | 7th Trade Hub')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-marketing mx-auto px-5 sm:px-6">
        <div class="glassmorphism rounded-3xl p-10 lg:p-16 border border-white/10">
            <div class="flex items-center gap-4 mb-8">
                <div class="size-14 rounded-xl bg-primary/20 flex items-center justify-center text-primary">
                    <x-ui.icon name="code" class="w-10 h-10" />
                </div>
                <div>
                    <h1 class="text-3xl lg:text-4xl font-bold text-white font-display">Code &amp; API</h1>
                    <p class="text-slate-400 mt-1">Integrate with 7th Trade Hub programmatically.</p>
                </div>
            </div>
            <p class="text-slate-400 max-w-2xl mb-8">
                Use our API for exchange, marketplace, and document services. Documentation and API keys are available from your dashboard once you're signed in.
            </p>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-accent text-white px-6 py-3 rounded-xl font-bold transition-all">
                    Go to Dashboard
                    <x-ui.icon name="arrow-right" class="w-5 h-5" />
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-accent text-white px-6 py-3 rounded-xl font-bold transition-all">
                    Get Started
                    <x-ui.icon name="arrow-right" class="w-5 h-5" />
                </a>
            @endauth
        </div>
    </div>
</section>
@endsection
