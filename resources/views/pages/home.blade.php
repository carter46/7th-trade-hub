@extends('layouts.marketing')

@section('title', $siteHeading ?? 'The Ultimate Digital Service Marketplace')

@section('content')
    @php
        $ecosystemItems = $ecosystemItems ?? [];
        $brandName = $siteName ?? config('app.name', '7th Trade Hub');
        $faqs = [
            [
                'q' => 'What is '.$brandName.'?',
                'a' => $brandName.' is a digital services hub. Browse VPN, domains, website packages, receipts, business documents, social tools, and more — plus a standalone crypto-to-cash exchange.',
            ],
            [
                'q' => 'What services can I browse?',
                'a' => 'Open Services to explore categories like Network, Communication, Website Services, Social Media, and Documents. Each card is a live service with its own products and pricing.',
            ],
            [
                'q' => 'How do website packages work?',
                'a' => 'Pick a package such as Online Banking website, review the demo and deliverables on the product page, then check out when you are ready. Support details are listed on each package.',
            ],
            [
                'q' => 'Can I buy domains and business documents here?',
                'a' => 'Yes. Register .com, .io, and .co domains, and download ready-to-edit receipts and business documents from the Documents & Receipts section.',
            ],
            [
                'q' => 'What is Crypto Cash Exchange?',
                'a' => 'It is a separate tool on the platform for swapping crypto to cash. It is not part of the main services catalog — open Exchange from the home page or main menu.',
            ],
            [
                'q' => 'Do you offer VPN, proxy, and email services?',
                'a' => 'Yes. Network and Communication categories include plans like Dedicated IP VPN, ISP Proxy Bundle, Dedicated SMTP IP, business email, and virtual phone numbers.',
            ],
            [
                'q' => 'How do I find the right plan?',
                'a' => 'Start from Services, open a category, then choose the service that matches your need. Each service page lists available products with descriptions and prices.',
            ],
            [
                'q' => 'Is the marketplace open yet?',
                'a' => 'The public marketplace for third-party listings is coming soon. Register or sign in if you want to get ready to sell when it launches.',
            ],
            [
                'q' => 'How do I get help with an order or service?',
                'a' => 'Use the Help and Contact pages, or open a support ticket from your dashboard after you sign in. Include your order or service name so we can assist faster.',
            ],
        ];

        $faqs = array_slice($faqs, 0, 5);
        $heroSlides = [
            asset('assets/images/homeslider1.jpg'),
            asset('assets/images/homeslider2.jpg'),
            asset('assets/images/homeslider3.jpg'),
        ];
    @endphp

    <section
        class="relative isolate overflow-hidden flex items-center pt-24 pb-12 sm:pt-32 sm:pb-24 lg:pt-36 lg:pb-36"
        style="min-height: calc(100dvh - 5rem);"
        x-data="{
            current: 0,
            timer: null,
            init() {
                this.timer = setInterval(() => this.current = (this.current + 1) % {{ count($heroSlides) }}, 6000);
            },
            destroy() {
                if (this.timer) clearInterval(this.timer);
            }
        }"
    >
        {{-- Cross-fading hero backgrounds --}}
        @foreach($heroSlides as $index => $slide)
            <div
                x-show="current === {{ $index }}"
                x-transition:enter="transition-opacity duration-1000 ease-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-1000 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="pointer-events-none absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
                style="background-image: url('{{ $slide }}'); @if($index > 0) display: none; @endif"
                aria-hidden="true"
            ></div>
        @endforeach
        {{-- Dark wash over photo — keep texture visible, not blank --}}
        <div
            class="pointer-events-none absolute inset-0 z-[1]"
            style="background: linear-gradient(180deg, rgba(15, 23, 42, 0.78) 0%, rgba(15, 23, 42, 0.72) 50%, rgba(15, 23, 42, 0.82) 100%);"
            aria-hidden="true"
        ></div>
        <div class="pointer-events-none absolute top-0 right-0 z-[1] w-[600px] h-[600px] bg-primary/15 blur-[140px] rounded-full" aria-hidden="true"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 z-[1] w-[500px] h-[500px] bg-accent/10 blur-[120px] rounded-full" aria-hidden="true"></div>
        <div class="relative z-10 w-full max-w-marketing mx-auto px-5 sm:px-6">
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold mb-5 sm:mb-7 tracking-tight text-white leading-[1.15] font-display">
                    {{ $siteHeading ?? 'The Ultimate Digital Service Marketplace' }}
                </h1>
                <p class="mx-auto max-w-xl text-slate-400 text-sm sm:text-base lg:text-lg mb-8 sm:mb-10 leading-relaxed">
                    Buy and sell digital services, swap crypto to cash, grow social accounts, and get ready-made templates — all in one hub.
                </p>
                <div class="mx-auto grid max-w-sm grid-cols-2 gap-3">
                    <a class="px-3 py-3 text-center text-sm sm:text-base bg-primary hover:bg-accent text-white font-bold rounded-xl shadow-xl transition-all hover:scale-[1.02] animate-glow" href="{{ route('services') }}">
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
                <p class="text-slate-400 text-base sm:text-lg max-w-xl mx-auto">Swap crypto to cash, then browse each service in our catalog for plans, packages, and pricing.</p>
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

            <div class="mt-10 sm:mt-12 flex justify-center">
                <a
                    href="{{ route('services') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary hover:bg-accent text-white text-sm font-bold transition-colors shadow-lg"
                >
                    View all
                    <x-ui.icon name="arrow-right" class="w-4 h-4" />
                </a>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 lg:py-24 bg-slate-900/20">
        @php
            $tabletImage = asset('assets/images/tablet-black copy.png');
        @endphp
        <div class="max-w-marketing mx-auto px-5 sm:px-6">
            <div class="glassmorphism rounded-[2.5rem] sm:rounded-[3rem] border border-white/10 overflow-hidden shadow-xl">
                <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center p-8 sm:p-10 lg:p-14 rounded-[2rem] sm:rounded-[2.5rem]">
                    <div class="order-2 lg:order-1 text-center lg:text-left">
                        <p class="text-accent text-xs sm:text-sm font-bold uppercase tracking-widest mb-3">Mobile &amp; desktop app</p>
                        <h2 class="text-3xl sm:text-4xl font-bold mb-4 font-display">Your hub, anywhere</h2>
                        <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-8 max-w-lg mx-auto lg:mx-0">
                            Install {{ $brandName }} on your phone or computer for quick access to services, wallet checkout, and your dashboard — right from your home screen or desktop. No APK file required.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                            <button
                                type="button"
                                data-pwa-install="mobile"
                                aria-label="Download {{ $brandName }} mobile app"
                                class="inline-flex items-center justify-center sm:justify-start gap-3 px-6 py-3.5 rounded-2xl bg-primary hover:bg-accent text-white text-sm font-bold transition-colors shadow-lg text-left"
                            >
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15">
                                    <x-ui.icon name="smartphone" class="w-5 h-5" />
                                </span>
                                <span class="inline-flex flex-col items-start gap-0.5 min-w-0">
                                    <span data-pwa-label>Download Mobile app</span>
                                    <span data-pwa-sub class="text-xs font-normal text-white/80">Install to your home screen</span>
                                </span>
                            </button>
                            <button
                                type="button"
                                data-pwa-install="desktop"
                                aria-label="Download {{ $brandName }} desktop app"
                                class="inline-flex items-center justify-center sm:justify-start gap-3 px-6 py-3.5 rounded-2xl border border-white/15 hover:border-accent/40 hover:bg-white/5 text-white text-sm font-bold transition-colors text-left"
                            >
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10">
                                    <x-ui.icon name="monitor" class="w-5 h-5" />
                                </span>
                                <span class="inline-flex flex-col items-start gap-0.5 min-w-0">
                                    <span data-pwa-label>Download Desktop app</span>
                                    <span data-pwa-sub class="text-xs font-normal text-slate-400">Install to your computer</span>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="order-1 lg:order-2 flex justify-center lg:justify-end">
                        <div class="rounded-[1.75rem] sm:rounded-[2rem] overflow-hidden bg-slate-900/40 p-3 sm:p-4 border border-white/5">
                            <img
                                src="{{ $tabletImage }}"
                                alt="{{ $brandName }} app on a tablet"
                                class="w-full max-w-md lg:max-w-lg h-auto drop-shadow-2xl rounded-xl sm:rounded-2xl"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20 lg:py-24">
        <div class="max-w-marketing mx-auto px-5 sm:px-6">
            <div class="mx-auto max-w-3xl">
                <div class="text-center mb-12 sm:mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4 font-display">Common Questions</h2>
                    <p class="text-slate-400">Quick answers about our services, website packages, domains, and platform features.</p>
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
                    <p class="text-slate-400 mb-8 sm:mb-10 max-w-xl mx-auto text-lg">Join thousands of entrepreneurs and traders leveraging the {{ $brandName }} ecosystem for their digital growth.</p>
                    <a class="px-10 py-5 bg-white text-dark font-bold rounded-2xl hover:bg-slate-200 transition-all shadow-xl font-display inline-block" href="{{ route('register') }}">
                        Create Your Free Account
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
