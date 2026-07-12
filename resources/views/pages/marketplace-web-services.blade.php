@extends('layouts.marketing')

@section('title', 'Web Services Marketplace | 7th Trade Hub')

@section('content')
<section class="relative overflow-hidden py-20 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="flex flex-col gap-8">
                <span class="text-primary font-bold tracking-widest text-xs uppercase">Premium Digital Solutions</span>
                <h1 class="text-4xl md:text-6xl font-black leading-tight tracking-tight text-white">
                    Professional Web Development &amp; Digital Assets
                </h1>
                <p class="text-lg text-slate-400 max-w-xl">
                    Custom web development services and premium ready-made website listings tailored for your business growth. Start your digital journey with proven architecture.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('marketplace') }}" class="bg-primary hover:bg-accent text-white px-8 py-4 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-primary/20">
                        Explore Marketplace
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                    <a href="{{ route('services') }}" class="bg-slate-800 hover:bg-slate-700 text-white px-8 py-4 rounded-xl font-bold transition-all border border-slate-700">
                        Request Custom Dev
                    </a>
                </div>
            </div>
            <div class="relative">
                <div class="absolute -inset-4 bg-primary/20 blur-3xl rounded-full"></div>
                <div class="relative aspect-video rounded-2xl overflow-hidden shadow-2xl border border-slate-700 glassmorphism flex items-center justify-center">
                    <span class="material-symbols-outlined text-6xl text-primary/50">deployed_code</span>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-20 bg-slate-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-white mb-4">Tailored Digital Solutions</h2>
            <p class="text-slate-400 max-w-2xl mx-auto">Web development, templates, and marketplace listings in one place.</p>
        </div>
    </div>
</section>
@endsection
