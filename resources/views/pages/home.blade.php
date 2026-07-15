@extends('layouts.marketing')

@section('title', '7th Trade Hub | The Ultimate Digital Service Marketplace')

@section('content')
    @php
        $ecosystemItems = [
            [
                'icon' => 'bitcoin',
                'title' => 'Crypto Cash Exchange',
                'body' => 'Turn crypto into cash fast. Safe swaps and quick payouts.',
                'href' => route('services'),
            ],
            [
                'icon' => 'analytics',
                'title' => 'Social Media Growth',
                'body' => 'Grow followers and reach with real engagement tools.',
                'href' => route('services'),
            ],
            [
                'icon' => 'listings',
                'title' => 'Docs & Templates',
                'body' => 'Ready-made business docs you can download and use.',
                'href' => route('document-templates'),
            ],
            [
                'icon' => 'inventory',
                'title' => 'Website Listings',
                'body' => 'Buy or sell websites with escrow to protect both sides.',
                'href' => route('website-listings'),
            ],
        ];

        $faqs = [
            [
                'q' => 'What can I do on 7th Trade Hub?',
                'a' => 'You can fund your NGN wallet, buy or sell digital services, swap crypto to cash, grow social accounts, download templates, and buy or sell websites — all in one place.',
            ],
            [
                'q' => 'How does the NGN wallet work?',
                'a' => 'Your wallet holds Naira balance for marketplace buys. Fund it by bank transfer or crypto sell, then pay for orders from your available balance.',
            ],
            [
                'q' => 'How do I deposit money?',
                'a' => 'Go to Deposit, pick bank transfer or crypto sell, follow the steps, and wait for approval. Funds show in your wallet when confirmed.',
            ],
            [
                'q' => 'How do withdrawals work?',
                'a' => 'Request a withdrawal from your wallet, add your bank details, and submit. We review it and send the funds when approved.',
            ],
            [
                'q' => 'What is escrow and why do I need it?',
                'a' => 'Escrow holds the buyer’s money until the seller delivers. It protects both sides on marketplace deals.',
            ],
            [
                'q' => 'How do I buy from the marketplace?',
                'a' => 'Open a listing, click buy, and pay from your wallet. Funds go into escrow until you confirm delivery.',
            ],
            [
                'q' => 'How do I sell my own listing?',
                'a' => 'Create a listing in your dashboard, wait for review if needed, then buyers can order. You get paid after delivery is confirmed.',
            ],
            [
                'q' => 'Do I need KYC to use the platform?',
                'a' => 'Basic browsing is open. Wallet actions, higher limits, and some features need KYC. Submit ID from the KYC page in your dashboard.',
            ],
            [
                'q' => 'How does crypto-to-cash work?',
                'a' => 'Start a crypto sell request, send the amount we quote, and after confirmation we credit NGN to your wallet.',
            ],
            [
                'q' => 'What social growth services do you offer?',
                'a' => 'We offer tools and packages to grow reach and engagement on major social platforms. Check Services for what is available now.',
            ],
            [
                'q' => 'Are document templates ready to use?',
                'a' => 'Yes. Browse templates, buy if paid, then download. Most are ready right after purchase.',
            ],
            [
                'q' => 'How do website listings work?',
                'a' => 'Sellers list sites with details and price. Buyers pay through escrow. Ownership transfer completes after both sides finish their steps.',
            ],
            [
                'q' => 'Are website listings verified?',
                'a' => 'Listings go through review. We check ownership claims and key details before they go live when review is required.',
            ],
            [
                'q' => 'How do fees work?',
                'a' => 'Fees depend on the service — exchange, marketplace, or withdrawal. You will see the fee before you confirm any payment.',
            ],
            [
                'q' => 'How do I contact support?',
                'a' => 'Open a support ticket from your dashboard, or use the Contact / Help pages. We reply as soon as we can.',
            ],
        ];

        $faqs = array_slice($faqs, 0, 5);
    @endphp

    <section class="relative overflow-hidden pt-28 pb-20 sm:pt-32 sm:pb-24 lg:min-h-[calc(100vh-5rem)] lg:flex lg:items-center lg:pt-36 lg:pb-36">
        {{-- Subtle photo under dark overlay --}}
        <div class="pointer-events-none absolute inset-0 -z-20" aria-hidden="true">
            <img
                src="{{ asset('assets/images/Image_ro410gro410gro41.png') }}"
                alt=""
                class="h-full w-full object-cover opacity-[0.18]"
            >
        </div>
        <div class="pointer-events-none absolute inset-0 -z-10 bg-dark/88" aria-hidden="true"></div>
        <div class="absolute top-0 right-0 -z-10 w-[600px] h-[600px] bg-primary/20 blur-[140px] rounded-full"></div>
        <div class="absolute bottom-0 left-0 -z-10 w-[500px] h-[500px] bg-accent/10 blur-[120px] rounded-full"></div>
        <div class="relative w-full max-w-marketing mx-auto px-5 sm:px-6">
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold mb-5 sm:mb-7 tracking-tight text-white leading-[1.15] font-display">
                    The Ultimate <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent to-primary">Digital Service</span> Marketplace
                </h1>
                <p class="mx-auto max-w-xl text-slate-400 text-sm sm:text-base lg:text-lg mb-8 sm:mb-10 leading-relaxed">
                    Buy and sell digital services, swap crypto to cash, grow social accounts, and get ready-made templates — all in one hub.
                </p>
                <div class="mx-auto grid max-w-sm grid-cols-2 gap-3">
                    <a class="px-3 py-3 text-center text-sm sm:text-base bg-primary hover:bg-accent text-white font-bold rounded-xl shadow-xl transition-all hover:scale-[1.02] animate-glow" href="{{ route('register') }}">
                        Get Started
                    </a>
                    <a class="px-3 py-3 text-center text-sm sm:text-base glassmorphism hover:bg-white/10 text-white font-bold rounded-xl border border-white/20 transition-all" href="{{ route('marketplace') }}">
                        Marketplace
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 lg:py-24 bg-slate-900/30">
        <div class="max-w-marketing mx-auto px-5 sm:px-6">
            <div class="text-center mb-10 sm:mb-14">
                <h2 class="text-3xl sm:text-4xl font-bold mb-3 font-display">What we do</h2>
                <p class="text-slate-400 text-base sm:text-lg max-w-xl mx-auto">Crypto, social growth, docs, and website deals — simple tools that help you trade and grow.</p>
            </div>

            <div x-data="ecosystemSlider" class="relative">
                <div
                    x-ref="track"
                    class="flex gap-4 overflow-x-auto scrollbar-hide overscroll-x-contain cursor-grab active:cursor-grabbing md:cursor-default md:grid md:grid-cols-3 lg:grid-cols-4 md:gap-5 md:overflow-visible"
                >
                    @foreach ($ecosystemItems as $item)
                        <div class="glassmorphism w-[calc(50%-0.5rem)] shrink-0 p-5 sm:p-6 rounded-2xl hover:border-accent/40 transition-all group flex flex-col md:w-auto md:shrink md:min-w-0">
                            <div class="w-12 h-12 mb-4 bg-accent/10 rounded-2xl flex items-center justify-center text-accent group-hover:bg-accent group-hover:text-white transition-all">
                                <x-ui.icon :name="$item['icon']" class="w-6 h-6" />
                            </div>
                            <h3 class="text-base sm:text-lg font-bold mb-2">{{ $item['title'] }}</h3>
                            <p class="text-slate-400 text-sm leading-relaxed mb-5 flex-1">{{ $item['body'] }}</p>
                            <a class="text-accent font-bold text-sm flex items-center gap-2 group/link mt-auto" href="{{ $item['href'] }}">
                                Learn More <x-ui.icon name="arrow-right" class="w-4 h-4 group-hover/link:translate-x-1 transition-transform" />
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex items-center justify-center gap-2 md:hidden" aria-label="Slide position">
                    <template x-for="i in dotCount" :key="i">
                        <button
                            type="button"
                            class="h-2 rounded-full transition-all duration-300"
                            :class="active === (i - 1) ? 'w-6 bg-accent' : 'w-2 bg-white/25 hover:bg-white/40'"
                            :aria-label="'Go to slide ' + i"
                            :aria-current="active === (i - 1) ? 'true' : 'false'"
                            @click="goTo(i - 1)"
                        ></button>
                    </template>
                </div>
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
                    <p class="text-slate-400">Quick answers about wallets, marketplace, crypto, social, docs, and more.</p>
                </div>
                <div class="space-y-3">
                    @foreach ($faqs as $index => $faq)
                        <details class="group glassmorphism rounded-2xl overflow-hidden border-white/5" @if($index === 0) open @endif>
                            <summary class="flex items-center justify-between gap-3 p-4 sm:p-5 cursor-pointer hover:bg-white/5 list-none">
                                <span class="font-bold text-sm sm:text-base text-left">{{ $faq['q'] }}</span>
                                <x-ui.icon name="chevron-down" class="w-5 h-5 shrink-0 transition-transform group-open:rotate-180" />
                            </summary>
                            <div class="px-4 sm:px-5 pb-4 sm:pb-5 text-slate-400 text-sm leading-relaxed">
                                {{ $faq['a'] }}
                            </div>
                        </details>
                    @endforeach
                </div>
                <div class="mt-8 text-center">
                    <a
                        href="{{ route('help') }}"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-white/15 text-sm font-bold text-white hover:bg-white/5 hover:border-accent/40 transition-all"
                    >
                        See more
                        <x-ui.icon name="arrow-right" class="w-4 h-4" />
                    </a>
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
