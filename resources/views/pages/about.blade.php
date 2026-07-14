@extends('layouts.marketing')

@section('title', 'About Us | 7th Trade Hub')

@section('content')
    <!-- From prototype-archive/about_us.html (main content only) -->
    <section class="relative h-[600px] flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-b from-background-dark/40 via-background-dark/80 to-background-dark z-10"></div>
            <img alt="Digital trading abstract background" class="w-full h-full object-cover scale-110 opacity-40" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCB4drbiBc6PlCMFZRVk320s_p3Jc4tkG9qPoLxbeD4WvigPr0nkiyqSIC2TgC4uTPCIR2KDlFkg7iCr3sDQT7yeZBWUfhTrBEkdCMxGOypd5QLKUCL8n2rPfYs_uL98_bVC14ybw0zQ7NvpjVOzW9Q6qame4yb6q77r84h1Ws_Yyefta1DEjra5V2kV2Cvag7az_WcQo6iAM_5xO5IzQP-1xSBRwB1FUgRF3eLZJKwrucfzWMwKRggZ5WTJhzAyfw5UweHmGk6QPY"/>
        </div>
        <div class="relative z-20 text-center px-6 max-w-4xl">
            <span class="text-accent font-bold tracking-[0.2em] uppercase text-xs mb-6 block">Pioneering the Digital Frontier</span>
            <h2 class="text-5xl md:text-7xl font-display font-extrabold mb-8 leading-[1.1] text-text-primary">Empowering the Future of Global Commerce</h2>
            <p class="text-lg md:text-xl text-text-secondary max-w-2xl mx-auto leading-relaxed">
                At 7th Trade Hub, we bridge the gap between traditional finance and the digital revolution through transparency, security, and innovation.
            </p>
        </div>
    </section>

    <section class="py-32 px-6 max-w-marketing mx-auto">
        <div class="grid lg:grid-cols-2 gap-20 items-center">
            <div class="order-2 lg:order-1">
                <h3 class="text-accent font-bold text-xs uppercase tracking-widest mb-4">Our Mission</h3>
                <h2 class="text-4xl md:text-5xl font-display font-bold mb-8 leading-tight">Redefining how the world trades digital assets.</h2>
                <p class="text-lg text-text-secondary mb-10 leading-relaxed">
                    We are on a journey to democratize access to global markets. By leveraging cutting-edge blockchain technology and intuitive design, we've built a hub where every trader—from novice to institutional—can flourish.
                </p>
                <div class="space-y-6">
                    <div class="flex items-start gap-5 p-6 rounded-2xl bg-card-dark/40 border border-white/5 backdrop-blur-sm hover:border-accent/30 transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center flex-shrink-0">
                            <x-ui.icon name="lock" class="w-5 h-5 text-accent" />
                        </div>
                        <div>
                            <h4 class="font-display font-bold text-text-primary mb-1">Institutional Security</h4>
                            <p class="text-sm text-text-secondary">Multilayered protection for every transaction and asset using the industry's most advanced protocols.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-5 p-6 rounded-2xl bg-card-dark/40 border border-white/5 backdrop-blur-sm hover:border-accent/30 transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center flex-shrink-0">
                            <x-ui.icon name="monitoring" class="w-5 h-5 text-accent" />
                        </div>
                        <div>
                            <h4 class="font-display font-bold text-text-primary mb-1">Lightning Execution</h4>
                            <p class="text-sm text-text-secondary">Ultra-low latency trading engines designed for institutional-grade real-time results.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative group order-1 lg:order-2">
                <div class="absolute -inset-4 bg-accent/20 rounded-[2.5rem] blur-3xl group-hover:bg-accent/30 transition-all"></div>
                <div class="relative rounded-3xl p-3 bg-white/5 border border-white/10 backdrop-blur-sm overflow-hidden">
                    <img alt="Trading Dashboard" class="relative rounded-2xl shadow-2xl grayscale hover:grayscale-0 transition-all duration-700" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA6rmAotRYvBoPIex0ejlGpYXHlI59t-C45cDmBsx-9uIJTfo3u2RMkxlBw8FbJWbuLQruLjcQvP8Vj2zR3TYPBjYTnuDXTNPXNn6a1_rZIj0ediHvqCmwW5Tc45G_zptuAwd8AWGHu6hhIFUDDIfRC2OUq5CadV2siRKDKAZW8YPRdynjMayeo4_LK1qfjkayOHVUjrYuvuEL08VFhu26mBlIj_-Jxr50UIoQ7pVFw2WZjFd5w0Gv0yBtEnVZ5JrLUnM4O2nY0m4Q"/>
                </div>
            </div>
        </div>
    </section>
@endsection

