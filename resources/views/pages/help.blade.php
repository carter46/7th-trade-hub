@extends('layouts.marketing')

@section('title', 'Help Center | 7th Trade Hub')

@section('content')
    <!-- From prototype-archive/help_center.html (main content only) -->
    <section class="py-16 sm:py-24 px-4 bg-gradient-to-b from-primary/10 to-transparent">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl sm:text-5xl font-black mb-6 tracking-tight text-text-primary">How can we help you?</h1>
            <p class="text-lg text-text-secondary mb-10">Search our knowledge base or get in touch with our specialist teams.</p>
            <div class="relative group">
                <span class="material-symbols-outlined absolute left-5 top-1/2 -translate-y-1/2 text-primary text-2xl">search</span>
                <input class="w-full pl-14 pr-6 py-5 bg-card-dark/50 backdrop-blur-sm border border-slate-700/50 rounded-2xl shadow-2xl text-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all text-text-primary placeholder:text-text-secondary/50" placeholder="Search for articles, guides, and more..." type="text"/>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 py-16">
        <h2 class="text-2xl font-bold mb-8 text-text-primary">Browse Categories</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="p-6 bg-card-dark border border-slate-800 rounded-2xl hover:border-primary/50 transition-colors group cursor-pointer shadow-lg shadow-black/20">
                <div class="size-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-primary group-hover:text-white">rocket_launch</span>
                </div>
                <h3 class="text-lg font-bold mb-2">Getting Started</h3>
                <p class="text-text-secondary text-sm">Account setup, onboarding, and first steps.</p>
            </div>
            <div class="p-6 bg-card-dark border border-slate-800 rounded-2xl hover:border-primary/50 transition-colors group cursor-pointer shadow-lg shadow-black/20">
                <div class="size-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-primary group-hover:text-white">currency_bitcoin</span>
                </div>
                <h3 class="text-lg font-bold mb-2">Crypto Exchange</h3>
                <p class="text-text-secondary text-sm">Rates, limits, and transaction troubleshooting.</p>
            </div>
            <div class="p-6 bg-card-dark border border-slate-800 rounded-2xl hover:border-primary/50 transition-colors group cursor-pointer shadow-lg shadow-black/20">
                <div class="size-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-primary group-hover:text-white">support_agent</span>
                </div>
                <h3 class="text-lg font-bold mb-2">Support</h3>
                <p class="text-text-secondary text-sm">Contact options and issue resolution.</p>
            </div>
        </div>
    </section>
@endsection

