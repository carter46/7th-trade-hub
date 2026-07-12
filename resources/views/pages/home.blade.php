@extends('layouts.marketing')

@section('title', '7th Trade Hub | The Ultimate Digital Service Marketplace')

@section('content')
    <div class="bg-slate-900/80 border-b border-white/5 overflow-hidden whitespace-nowrap py-3">
        <div class="flex crypto-ticker">
            <div class="flex items-center gap-8 px-4">
                <span class="flex items-center gap-2"><span class="font-bold">BTC:</span> <span class="text-accent">$64,231.50</span> <span class="text-xs text-accent">↑ 1.2%</span></span>
                <span class="flex items-center gap-2"><span class="font-bold">ETH:</span> <span class="text-accent">$3,452.12</span> <span class="text-xs text-accent">↑ 0.8%</span></span>
                <span class="flex items-center gap-2"><span class="font-bold">USDT:</span> <span class="text-slate-300">$1.00</span> <span class="text-xs text-slate-500">0.0%</span></span>
                <span class="flex items-center gap-2"><span class="font-bold">SOL:</span> <span class="text-red-400">$142.88</span> <span class="text-xs text-red-400">↓ 2.1%</span></span>
                <span class="flex items-center gap-2"><span class="font-bold">BNB:</span> <span class="text-accent">$582.40</span> <span class="text-xs text-accent">↑ 0.5%</span></span>
            </div>
            <div class="flex items-center gap-8 px-4">
                <span class="flex items-center gap-2"><span class="font-bold">BTC:</span> <span class="text-accent">$64,231.50</span> <span class="text-xs text-accent">↑ 1.2%</span></span>
                <span class="flex items-center gap-2"><span class="font-bold">ETH:</span> <span class="text-accent">$3,452.12</span> <span class="text-xs text-accent">↑ 0.8%</span></span>
                <span class="flex items-center gap-2"><span class="font-bold">USDT:</span> <span class="text-slate-300">$1.00</span> <span class="text-xs text-slate-500">0.0%</span></span>
                <span class="flex items-center gap-2"><span class="font-bold">SOL:</span> <span class="text-red-400">$142.88</span> <span class="text-xs text-red-400">↓ 2.1%</span></span>
                <span class="flex items-center gap-2"><span class="font-bold">BNB:</span> <span class="text-accent">$582.40</span> <span class="text-xs text-accent">↑ 0.5%</span></span>
            </div>
        </div>
    </div>

    <section class="relative overflow-hidden py-24 lg:py-32">
        <div class="absolute top-0 right-0 -z-10 w-[600px] h-[600px] bg-primary/20 blur-[140px] rounded-full"></div>
        <div class="absolute bottom-0 left-0 -z-10 w-[500px] h-[500px] bg-accent/10 blur-[120px] rounded-full"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-block px-4 py-1.5 mb-6 glassmorphism rounded-full border border-accent/20">
                        <span class="text-accent text-sm font-semibold tracking-wide uppercase">New: Crypto Exchange v2.0 Live</span>
                    </div>
                    <h1 class="text-5xl lg:text-7xl font-extrabold mb-8 tracking-tight text-white leading-[1.1] font-display">
                        The Ultimate <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent to-primary">Digital Service</span> Marketplace
                    </h1>
                    <p class="max-w-2xl mx-auto lg:mx-0 text-slate-400 text-lg lg:text-xl mb-12 leading-relaxed">
                        Scale your digital presence with elite social services, secure crypto-to-cash exchanges, professional document templates, and premium website listings all in one hub.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a class="px-8 py-4 bg-primary hover:bg-accent text-white font-bold rounded-xl shadow-2xl transition-all hover:scale-105 animate-glow" href="{{ route('register') }}">
                            Get Started
                        </a>
                        <a class="px-8 py-4 glassmorphism hover:bg-white/10 text-white font-bold rounded-xl border border-white/20 transition-all" href="{{ route('marketplace') }}">
                            Explore Marketplace
                        </a>
                    </div>
                </div>

                <div class="glassmorphism p-8 rounded-[2.5rem] border-white/10 relative">
                    <div class="absolute -top-4 -right-4 w-20 h-20 bg-accent/20 blur-2xl rounded-full"></div>
                    <h3 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-accent">currency_exchange</span>
                        Crypto to Cash
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">You Send</label>
                            <div class="flex bg-slate-900/50 rounded-xl border border-white/5 overflow-hidden">
                                <input class="w-full bg-transparent border-none text-xl font-bold px-4 focus:ring-0" type="number" value="1.00"/>
                                <select class="bg-slate-800 border-none text-sm font-bold px-4 focus:ring-0">
                                    <option>BTC</option>
                                    <option>ETH</option>
                                    <option>USDT</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-center -my-2">
                            <div class="w-10 h-10 bg-accent rounded-full flex items-center justify-center shadow-lg z-10">
                                <span class="material-symbols-outlined text-white">swap_vert</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">You Receive (Est.)</label>
                            <div class="flex bg-slate-900/50 rounded-xl border border-white/5 overflow-hidden">
                                <input class="w-full bg-transparent border-none text-xl font-bold px-4 focus:ring-0" readonly type="text" value="64,231.50"/>
                                <select class="bg-slate-800 border-none text-sm font-bold px-4 focus:ring-0">
                                    <option>USD</option>
                                    <option>EUR</option>
                                    <option>GBP</option>
                                </select>
                            </div>
                        </div>
                        <a class="w-full py-4 bg-white text-dark font-bold rounded-xl hover:bg-slate-200 transition-all mt-4 text-center block" href="{{ route('services') }}">
                            Swap Now
                        </a>
                        <p class="text-center text-[10px] text-slate-500 uppercase tracking-widest mt-4 font-bold">No hidden fees • 5-minute settlement</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="relative">
                    <div class="aspect-square glassmorphism rounded-3xl p-2 relative overflow-hidden">
                        <img alt="Digital Trading" class="w-full h-full object-cover rounded-2xl opacity-80" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA3RSggnEQgSG3yhLu30HDq6XJowk4lbcEcfl8kj-BTV-npLc7SX4TYYpneqOskqzEH22IkgLOGBnsCf1ree4-pDZ931m0PvGD4vOWrwwyXfn9vkfTAAR_RLMPVVJN94IDlm68BVUNwLcNSv1KzFb4k0emrdV3I0EWGk5Qmmfqw1JenjE7KjTVeUEFlQ36aO1bxKShLzzOWaFpon7w_1DNGgA2Ij9Qo0BnBN9oxNWTs0IugMjiGSmENM23v4ZaDOCrl16P0PsWZLGk"/>
                        <div class="absolute inset-0 bg-gradient-to-t from-dark via-transparent to-transparent"></div>
                    </div>
                    <div class="absolute -bottom-6 -right-6 glassmorphism p-6 rounded-2xl border-accent/20 hidden md:block">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-accent rounded-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-white">verified_user</span>
                            </div>
                            <div>
                                <div class="text-sm font-bold">Secured by Escrow</div>
                                <div class="text-xs text-slate-400">Trusted by 15k+ traders</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h2 class="text-accent font-bold uppercase tracking-widest text-sm mb-4">Our Mission</h2>
                    <h3 class="text-4xl lg:text-5xl font-bold mb-8 font-display">Bridging Crypto with the Digital Economy</h3>
                    <p class="text-slate-400 text-lg mb-6 leading-relaxed">
                        At 7th Trade Hub, we believe that digital assets and professional services shouldn't exist in silos. Our platform acts as a unified bridge, allowing entrepreneurs to leverage their crypto assets to build, scale, and sell digital businesses seamlessly.
                    </p>
                    <p class="text-slate-400 text-lg mb-8 leading-relaxed">
                        Whether you're looking to exchange assets, grow your brand's social footprint, or acquire established digital properties, we provide the secure infrastructure needed to trade with absolute confidence.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-accent">check_circle</span>
                            <span class="text-sm font-semibold">Decentralized Trust</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-accent">check_circle</span>
                            <span class="text-sm font-semibold">24/7 Expert Support</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-accent">check_circle</span>
                            <span class="text-sm font-semibold">Global Compliance</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-accent">check_circle</span>
                            <span class="text-sm font-semibold">Lightning Fast Trade</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-slate-900/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-bold mb-4 font-display">Core Ecosystem</h2>
                <p class="text-slate-400 text-lg max-w-2xl mx-auto">Discover the four pillars of our platform designed to empower your digital journey.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="glassmorphism p-10 rounded-[2.5rem] hover:border-accent/40 transition-all group flex flex-col md:flex-row gap-8">
                    <div class="w-16 h-16 shrink-0 bg-accent/10 rounded-2xl flex items-center justify-center text-accent group-hover:bg-accent group-hover:text-white transition-all">
                        <span class="material-symbols-outlined text-4xl">currency_bitcoin</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold mb-4">Crypto Cash Exchange</h3>
                        <p class="text-slate-400 mb-6 leading-relaxed">The most secure way to bridge your digital assets. We offer peer-to-peer and automated exchange services with the lowest market spreads and instant payouts to global bank accounts or e-wallets.</p>
                        <ul class="space-y-2 mb-8 text-sm text-slate-300">
                            <li class="flex items-center gap-2"><div class="w-1 h-1 bg-accent rounded-full"></div> 100+ Crypto Pairs Supported</li>
                            <li class="flex items-center gap-2"><div class="w-1 h-1 bg-accent rounded-full"></div> SEPA, Swift, and Mobile Money</li>
                        </ul>
                        <a class="text-accent font-bold flex items-center gap-2 group/link" href="{{ route('services') }}">
                            Learn More <span class="material-symbols-outlined group-hover/link:translate-x-1 transition-transform">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <div class="glassmorphism p-10 rounded-[2.5rem] hover:border-accent/40 transition-all group flex flex-col md:flex-row gap-8">
                    <div class="w-16 h-16 shrink-0 bg-accent/10 rounded-2xl flex items-center justify-center text-accent group-hover:bg-accent group-hover:text-white transition-all">
                        <span class="material-symbols-outlined text-4xl">trending_up</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold mb-4">Social Media Growth</h3>
                        <p class="text-slate-400 mb-6 leading-relaxed">Dominate the algorithm with our proprietary growth strategies. From high-retention engagement to full account management, we help brands and influencers reach their peak potential audience.</p>
                        <ul class="space-y-2 mb-8 text-sm text-slate-300">
                            <li class="flex items-center gap-2"><div class="w-1 h-1 bg-accent rounded-full"></div> Targeted Organic Engagement</li>
                            <li class="flex items-center gap-2"><div class="w-1 h-1 bg-accent rounded-full"></div> Multi-platform Campaign Suite</li>
                        </ul>
                        <a class="text-accent font-bold flex items-center gap-2 group/link" href="{{ route('services') }}">
                            Learn More <span class="material-symbols-outlined group-hover/link:translate-x-1 transition-transform">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <div class="glassmorphism p-10 rounded-[2.5rem] hover:border-accent/40 transition-all group flex flex-col md:flex-row gap-8">
                    <div class="w-16 h-16 shrink-0 bg-accent/10 rounded-2xl flex items-center justify-center text-accent group-hover:bg-accent group-hover:text-white transition-all">
                        <span class="material-symbols-outlined text-4xl">description</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold mb-4">Doc &amp; Template Gen</h3>
                        <p class="text-slate-400 mb-6 leading-relaxed">Professionalize your business in minutes. Access a massive library of smart templates for SaaS agreements, contractor forms, and legal disclosures designed specifically for the digital industry.</p>
                        <ul class="space-y-2 mb-8 text-sm text-slate-300">
                            <li class="flex items-center gap-2"><div class="w-1 h-1 bg-accent rounded-full"></div> 500+ Industry-ready Docs</li>
                            <li class="flex items-center gap-2"><div class="w-1 h-1 bg-accent rounded-full"></div> AI-Powered Personalization</li>
                        </ul>
                        <a class="text-accent font-bold flex items-center gap-2 group/link" href="{{ route('document-templates') }}">
                            Learn More <span class="material-symbols-outlined group-hover/link:translate-x-1 transition-transform">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <div class="glassmorphism p-10 rounded-[2.5rem] hover:border-accent/40 transition-all group flex flex-col md:flex-row gap-8">
                    <div class="w-16 h-16 shrink-0 bg-accent/10 rounded-2xl flex items-center justify-center text-accent group-hover:bg-accent group-hover:text-white transition-all">
                        <span class="material-symbols-outlined text-4xl">domain</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold mb-4">Website Listings</h3>
                        <p class="text-slate-400 mb-6 leading-relaxed">Buy or sell income-generating digital assets. Our curated marketplace features high-authority domains, dropshipping stores, and established blogs with verified revenue and traffic data.</p>
                        <ul class="space-y-2 mb-8 text-sm text-slate-300">
                            <li class="flex items-center gap-2"><div class="w-1 h-1 bg-accent rounded-full"></div> Verified Revenue Reports</li>
                            <li class="flex items-center gap-2"><div class="w-1 h-1 bg-accent rounded-full"></div> Secure Escrow Transfers</li>
                        </ul>
                        <a class="text-accent font-bold flex items-center gap-2 group/link" href="{{ route('website-listings') }}">
                            Learn More <span class="material-symbols-outlined group-hover/link:translate-x-1 transition-transform">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 border-y border-white/5 bg-slate-900/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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

    <section class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4 font-display">Trusted by Founders</h2>
                <p class="text-slate-400">Join a community of successful digital entrepreneurs.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="glassmorphism p-8 rounded-3xl relative">
                    <div class="text-accent mb-6">
                        <span class="material-symbols-outlined">format_quote</span>
                    </div>
                    <p class="text-slate-300 italic mb-8">"The crypto-to-cash exchange is the fastest I've ever used. Funds were in my account within minutes. Highly recommended for any digital nomad."</p>
                    <div class="flex items-center gap-4">
                        <img alt="User" class="w-12 h-12 rounded-full border-2 border-accent" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCekL2nn9ZAU1hsjiMnogTHnVbqhJPLjO9SWZbuouE6DI8EDEv9G5CSF6b0YsAlbW6ykZ8VGO5aoauJUrKGNw_MV-RiHBX4fsoTkHZWw92wQfSEMmlBCkI40lCLc5lPVQBly06x3KKnzWvhiY6949KzIyPOxSJVV9GsRp9jGE1-z3F4REtKCL4DLYBLgfjRK99R51zWww_SA6GSlrJaPr7aPv_gUx5Ulw-b1z7CiIJoFom6jra7VaXZkzMENi67_EsnEu8k8eYed8Y"/>
                        <div>
                            <div class="font-bold">Marcus Chen</div>
                            <div class="text-xs text-slate-500 font-semibold uppercase">E-commerce Founder</div>
                        </div>
                    </div>
                </div>
                <div class="glassmorphism p-8 rounded-3xl relative border-accent/30">
                    <div class="text-accent mb-6">
                        <span class="material-symbols-outlined">format_quote</span>
                    </div>
                    <p class="text-slate-300 italic mb-8">"Found an amazing SaaS property on their listings. The escrow process was professional and smooth. A total game changer for my portfolio."</p>
                    <div class="flex items-center gap-4">
                        <img alt="User" class="w-12 h-12 rounded-full border-2 border-accent" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB4osA7_XOxvhF6nDxoHVtkV7f54BP8qI7m_GSzWLOSCtWnqQQelBDR_0_yol9VD5kJVkWhbcm1hqCCToT6sqMB_0puS-CEUdOF_AYa4SfQBLu_H6uEvv8NDBo_h3Iy2XCOI62zVx5HI-E2IilTxmjQsT0c1PL8BNoyG5GETKkTRzCIT1dUBhdkxuLY33Cf1BPM2KlGsKjuyio6LCIIf3wgpv4woIFiGq5c5F310mwhwBpbOEeN13x3LfvWLENewtZxVXspACJK8Po"/>
                        <div>
                            <div class="font-bold">Sarah Jenkins</div>
                            <div class="text-xs text-slate-500 font-semibold uppercase">Digital Investor</div>
                        </div>
                    </div>
                </div>
                <div class="glassmorphism p-8 rounded-3xl relative">
                    <div class="text-accent mb-6">
                        <span class="material-symbols-outlined">format_quote</span>
                    </div>
                    <p class="text-slate-300 italic mb-8">"Their social growth tools helped us scale our Instagram from 5k to 50k in under three months. The engagement quality is unmatched."</p>
                    <div class="flex items-center gap-4">
                        <img alt="User" class="w-12 h-12 rounded-full border-2 border-accent" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCEFV7PJbbu8bqGt5fJq5QROQAtDV8izTr0nKTLqfRUetN0AtHY2sCfk4DzJZwJmuVc1u5ddiLN0lw6c2LYvHdIQIQFx8bpyzkfthc1Z4trn2olXZFM-1ZSiUwrFsmO9oLNllx4jvXKnkGwpJTRS811m6DavAaBV7EFUKGP9vDU8W19H9NnsNJhK3AWRSDiTaN1-JMozR3uaAtmmTgyCS4ciEIhoAcccm_bDPbGbO3SVvUgUIBhKeJLBczCW1M-wMkirFd4DzmzpMY"/>
                        <div>
                            <div class="font-bold">David Rivera</div>
                            <div class="text-xs text-slate-500 font-semibold uppercase">Marketing Agency CEO</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-slate-900/20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4 font-display">Common Questions</h2>
                <p class="text-slate-400">Everything you need to know about the 7th Trade Hub ecosystem.</p>
            </div>
            <div class="space-y-4">
                <details class="group glassmorphism rounded-2xl overflow-hidden border-white/5" open>
                    <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5 list-none">
                        <span class="font-bold text-lg">How secure are the crypto transactions?</span>
                        <span class="material-symbols-outlined transition-transform group-open:rotate-180">expand_more</span>
                    </summary>
                    <div class="px-6 pb-6 text-slate-400 leading-relaxed">
                        We use military-grade encryption and multi-sig cold storage for all assets. Every transaction is backed by our internal escrow system, ensuring funds are only released when both parties fulfill their obligations.
                    </div>
                </details>
                <details class="group glassmorphism rounded-2xl overflow-hidden border-white/5">
                    <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5 list-none">
                        <span class="font-bold text-lg">How long do service deliveries take?</span>
                        <span class="material-symbols-outlined transition-transform group-open:rotate-180">expand_more</span>
                    </summary>
                    <div class="px-6 pb-6 text-slate-400 leading-relaxed">
                        Crypto swaps are typically processed within 5-15 minutes. Social growth services begin within 24 hours of ordering, while document templates are available for instant download.
                    </div>
                </details>
                <details class="group glassmorphism rounded-2xl overflow-hidden border-white/5">
                    <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5 list-none">
                        <span class="font-bold text-lg">Is there a verification process for listings?</span>
                        <span class="material-symbols-outlined transition-transform group-open:rotate-180">expand_more</span>
                    </summary>
                    <div class="px-6 pb-6 text-slate-400 leading-relaxed">
                        Yes, every website listing goes through a rigorous vetting process where we verify domain ownership, traffic statistics via Analytics, and revenue through Stripe or PayPal integrations.
                    </div>
                </details>
            </div>
        </div>
    </section>

    <section class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glassmorphism p-12 lg:p-20 rounded-[3rem] text-center border-accent/20 relative overflow-hidden">
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-primary/20 blur-3xl rounded-full"></div>
                <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-accent/20 blur-3xl rounded-full"></div>
                <div class="relative z-10">
                    <h2 class="text-3xl lg:text-5xl font-bold mb-8 font-display">Ready to elevate your trade?</h2>
                    <p class="text-slate-400 mb-10 max-w-xl mx-auto text-lg">Join thousands of entrepreneurs and traders leveraging the 7th Trade Hub ecosystem for their digital growth.</p>
                    <a class="px-10 py-5 bg-white text-dark font-bold rounded-2xl hover:bg-slate-200 transition-all shadow-xl font-display inline-block" href="{{ route('register') }}">
                        Create Your Free Account
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

