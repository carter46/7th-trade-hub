@extends('layouts.marketing')

@section('title', 'Document Templates | 7th Trade Hub')

@section('content')
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-extrabold text-white tracking-tight mb-3">Document Templates</h1>
                <p class="text-slate-400 text-lg leading-relaxed">Select from our professionally curated legal and personal document templates. Start editing to generate your official PDF instantly.</p>
            </div>
            @auth
                <a href="{{ route('dashboard.documents') }}" class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl font-bold hover:bg-accent transition-all shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined">add_circle</span>
                    Custom Draft
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl font-bold hover:bg-accent transition-all shadow-lg shadow-primary/20">
                    Get Started
                </a>
            @endauth
        </div>
        <div class="glassmorphism rounded-3xl p-8 border border-white/10">
            <p class="text-slate-400 mb-6">Categories: Legal, Sales, Contracts, and more. Browse templates or create a custom draft from your dashboard.</p>
            <div class="flex flex-wrap gap-3">
                <span class="px-4 py-2 rounded-xl bg-primary/20 text-primary text-sm font-semibold">All Documents</span>
                <span class="px-4 py-2 rounded-xl bg-slate-800 text-slate-400 text-sm font-semibold">Legal</span>
                <span class="px-4 py-2 rounded-xl bg-slate-800 text-slate-400 text-sm font-semibold">Sales</span>
                <span class="px-4 py-2 rounded-xl bg-slate-800 text-slate-400 text-sm font-semibold">Contracts</span>
            </div>
        </div>
    </div>
</section>
@endsection
