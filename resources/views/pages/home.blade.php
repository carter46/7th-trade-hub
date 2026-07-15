@extends('layouts.marketing')

@section('title', '7th Trade Hub | The Ultimate Digital Service Marketplace')

@section('content')
    @php
        $ecosystemItems = [
            [
                'icon' => 'bitcoin',
                'title' => 'Crypto Cash Exchange',
                'body' => 'Secure crypto-to-cash with competitive spreads and fast payouts to bank accounts or e-wallets.',
                'href' => route('services'),
            ],
            [
                'icon' => 'analytics',
                'title' => 'Social Media Growth',
                'body' => 'High-retention engagement and campaign tools to grow brands and audiences across platforms.',
                'href' => route('services'),
            ],
            [
                'icon' => 'listings',
                'title' => 'Doc & Template Gen',
                'body' => 'Industry-ready templates for agreements, contractor forms, and digital-business disclosures.',
                'href' => route('document-templates'),
            ],
            [
                'icon' => 'inventory',
                'title' => 'Website Listings',
                'body' => 'Buy and sell verified digital assets with escrow-backed transfers and revenue reporting.',
                'href' => route('website-listings'),
            ],
        ];
    @endphp

    <section class="relative overflow-hidden py-14 sm:py-20 lg:py-28">
        <div class="absolute top-0 right-0 -z-10 w-[600px] h-[600px] bg-primary/20 blur-[140px] rounded-full"></div>
        <div class="absolute bottom-0 left-0 -z-10 w-[500px] h-[500px] bg-accent/10 blur-[120px] rounded-full"></div>
        <div class="max-w-marketing mx-auto px-5 sm:px-6">
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-6 sm:mb-8 tracking-tight text-white leading-[1.1] font-display">
                    The Ultimate <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent to-primary">Digital Service</span> Marketplace
                </h1>
                <p class="mx-auto max-w-2xl text-slate-400 text-base sm:text-lg lg:text-xl mb-8 sm:mb-12 leading-relaxed">
                    Scale your digital presence with elite social services, secure crypto-to-cash exchanges, professional document templates, and premium website listings all in one hub.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a class="px-8 py-4 bg-primary hover:bg-accent text-white font-bold rounded-xl shadow-2xl transition-all hover:scale-105 animate-glow" href="{{ route('register') }}">
                        Get Started
                    </a>
                    <a class="px-8 py-4 glassmorphism hover:bg-white/10 text-white font-bold rounded-xl border border-white/20 transition-all" href="{{ route('marketplace') }}">
                        Explore Marketplace
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 lg:py-24 bg-slate-900/30">
        <div class="max-w-marketing mx-auto px-5 sm:px-6">
            <div class="text-center mb-10 sm:mb-14">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4 font-display">Core Ecosystem</h2>
                <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto">Discover the four pillars of our platform designed to empower your digital journey.</p>
            </div>

            <div
                x-data="ecosystemSlider"
                class="flex gap-4 overflow-x-auto scrollbar-hide overscroll-x-contain cursor-grab active:cursor-grabbing md:cursor-default md:grid md:grid-cols-3 lg:grid-cols-4 md:gap-5 md:overflow-visible"
            >
                @foreach ($ecosystemItems as $item)
                    <div class="glassmorphism w-[calc(50%-0.5rem)] shrink-0 p-5 sm:p-6 rounded-2xl hover:border-accent/40 transition-all group flex flex-col md:w-auto md:shrink md:min-w-0">
                        <div class="w-12 h-12 mb-4 bg-accent/10 rounded-2xl flex items-center justify-center text-accent group-hover:bg-accent group-hover:text-white transition-all">
                            <x-ui.icon :name="$item['icon']" class="w-6 h-6" />
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ $item['title'] }}</h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-5 flex-1">{{ $item['body'] }}</p>
                        <a class="text-accent font-bold text-sm flex items-center gap-2 group/link mt-auto" href="{{ $item['href'] }}">
                            Learn More <x-ui.icon name="arrow-right" class="w-4 h-4 group-hover/link:translate-x-1 transition-transform" />
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 border-y border-white/5 bg-slate-900/50">
        <div class="max-w-marketing mx-auto px-5 sm:px-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-extrabold text-white mb-2 font-display">15k+</div>
                    <div class="text-slate-500 text-sm uppercase tracking-widest font-semibold">Active Users</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold text-white mb-2 font-display">50k+</div>
                    <div class="text-slate-500 text-sm uppercase tracking-widest font-semibold">Orders Completed</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold text-white mb-2 font-display">$4.2M</div>
                    <div class="text-slate-500 text-sm uppercase tracking-widest font-semibold">Trade Volume</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold text-white mb-2 font-display">99.9%</div>
                    <div class="text-slate-500 text-sm uppercase tracking-widest font-semibold">Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 lg:py-24">
        <div class="max-w-marketing mx-auto px-5 sm:px-6">
            <div class="mx-auto max-w-3xl">
                <div class="text-center mb-12 sm:mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4 font-display">Common Questions</h2>
                    <p class="text-slate-400">Everything you need to know about the 7th Trade Hub ecosystem.</p>
                </div>
                <div class="space-y-4">
                    <details class="group glassmorphism rounded-2xl overflow-hidden border-white/5" open>
                        <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5 list-none">
                            <span class="font-bold text-lg">How secure are the crypto transactions?</span>
                            <x-ui.icon name="chevron-down" class="w-5 h-5 transition-transform group-open:rotate-180" />
                        </summary>
                        <div class="px-6 pb-6 text-slate-400 leading-relaxed">
                            We use military-grade encryption and multi-sig cold storage for all assets. Every transaction is backed by our internal escrow system, ensuring funds are only released when both parties fulfill their obligations.
                        </div>
                    </details>
                    <details class="group glassmorphism rounded-2xl overflow-hidden border-white/5">
                        <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5 list-none">
                            <span class="font-bold text-lg">How long do service deliveries take?</span>
                            <x-ui.icon name="chevron-down" class="w-5 h-5 transition-transform group-open:rotate-180" />
                        </summary>
                        <div class="px-6 pb-6 text-slate-400 leading-relaxed">
                            Crypto swaps are typically processed within 5-15 minutes. Social growth services begin within 24 hours of ordering, while document templates are available for instant download.
                        </div>
                    </details>
                    <details class="group glassmorphism rounded-2xl overflow-hidden border-white/5">
                        <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5 list-none">
                            <span class="font-bold text-lg">Is there a verification process for listings?</span>
                            <x-ui.icon name="chevron-down" class="w-5 h-5 transition-transform group-open:rotate-180" />
                        </summary>
                        <div class="px-6 pb-6 text-slate-400 leading-relaxed">
                            Yes, every website listing goes through a rigorous vetting process where we verify domain ownership, traffic statistics via Analytics, and revenue through Stripe or PayPal integrations.
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 lg:py-24">
        <div class="max-w-marketing mx-auto px-5 sm:px-6">
            <div class="glassmorphism p-10 sm:p-12 lg:p-16 rounded-[3rem] text-center border-accent/20 relative overflow-hidden">
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-primary/20 blur-3xl rounded-full"></div>
                <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-accent/20 blur-3xl rounded-full"></div>
                <div class="relative z-10">
                    <h2 class="text-3xl lg:text-5xl font-bold mb-6 sm:mb-8 font-display">Ready to elevate your trade?</h2>
                    <p class="text-slate-400 mb-8 sm:mb-10 max-w-xl mx-auto text-lg">Join thousands of entrepreneurs and traders leveraging the 7th Trade Hub ecosystem for their digital growth.</p>
                    <a class="px-10 py-5 bg-white text-dark font-bold rounded-2xl hover:bg-slate-200 transition-all shadow-xl font-display inline-block" href="{{ route('register') }}">
                        Create Your Free Account
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
