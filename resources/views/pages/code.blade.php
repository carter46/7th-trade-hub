@extends('layouts.marketing')

@section('title', 'Code & API | 7th Trade Hub')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glassmorphism rounded-3xl p-10 lg:p-16 border border-white/10">
            <div class="flex items-center gap-4 mb-8">
                <div class="size-14 rounded-xl bg-primary/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-4xl">code</span>
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
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-accent text-white px-6 py-3 rounded-xl font-bold transition-all">
                    Get Started
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            @endauth
        </div>
    </div>
</section>
@endsection
