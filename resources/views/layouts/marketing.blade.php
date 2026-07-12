<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', '7th Trade Hub')</title>
        @PwaHead

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-dark text-slate-100 font-sans selection:bg-accent selection:text-white">
        <header class="fixed top-0 w-full z-50 glassmorphism border-b border-white/10">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <a class="flex items-center gap-2" href="{{ route('home') }}">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-accent rounded-lg flex items-center justify-center font-bold text-xl shadow-lg">7</div>
                    <span class="text-xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-400 font-display">7th Trade Hub</span>
                </a>

                <div class="hidden lg:flex items-center space-x-8 text-sm font-medium text-slate-300">
                    <a class="hover:text-accent transition-colors" href="{{ route('home') }}">Home</a>
                    <a class="hover:text-accent transition-colors" href="{{ route('marketplace') }}">Marketplace</a>
                    <a class="hover:text-accent transition-colors" href="{{ route('exchange') }}">Exchange</a>
                    <a class="hover:text-accent transition-colors" href="{{ route('services') }}">Services</a>
                    <a class="hover:text-accent transition-colors" href="{{ route('templates') }}">Templates</a>
                    <a class="hover:text-accent transition-colors" href="{{ route('website-listings') }}">Website Listings</a>
                    <a class="hover:text-accent transition-colors" href="{{ route('code') }}">Code</a>
                    <a class="hover:text-accent transition-colors" href="{{ route('help') }}">Help</a>
                </div>

                <div class="flex items-center gap-4">
                    @auth
                        <a class="text-slate-300 hover:text-white font-medium text-sm transition-colors" href="{{ route('dashboard') }}">Dashboard</a>
                    @else
                        <a class="text-slate-300 hover:text-white font-medium text-sm transition-colors" href="{{ route('login') }}">Login</a>
                        <a class="bg-primary hover:bg-accent px-5 py-2.5 rounded-full text-sm font-bold transition-all shadow-lg hover:scale-105 active:scale-95" href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
            </nav>
        </header>

        <main class="pt-20">
            @yield('content')
        </main>

        <footer class="bg-slate-950 pt-20 pb-10 border-t border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                    <div>
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-8 h-8 bg-primary rounded flex items-center justify-center font-bold">7</div>
                            <span class="text-xl font-bold font-display">7th Trade Hub</span>
                        </div>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Leading the digital marketplace revolution with secure, transparent, and efficient trade solutions for global users.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-white font-bold mb-6 font-display">Platform</h4>
                        <ul class="space-y-4 text-slate-500 text-sm">
                            <li><a class="hover:text-accent transition-colors" href="{{ route('marketplace') }}">Marketplace</a></li>
                            <li><a class="hover:text-accent transition-colors" href="{{ route('marketplace.web-services') }}">Web Services</a></li>
                            <li><a class="hover:text-accent transition-colors" href="{{ route('exchange') }}">Crypto Exchange</a></li>
                            <li><a class="hover:text-accent transition-colors" href="{{ route('templates') }}">Templates</a></li>
                            <li><a class="hover:text-accent transition-colors" href="{{ route('code') }}">Code &amp; API</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-bold mb-6 font-display">Support</h4>
                        <ul class="space-y-4 text-slate-500 text-sm">
                            <li><a class="hover:text-accent transition-colors" href="{{ route('help') }}">Help Center</a></li>
                            <li><a class="hover:text-accent transition-colors" href="{{ route('support') }}">Contact Us</a></li>
                            <li><a class="hover:text-accent transition-colors" href="{{ route('support') }}">API Docs</a></li>
                            <li><a class="hover:text-accent transition-colors" href="{{ route('support') }}">Status</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-bold mb-6 font-display">Legal</h4>
                        <ul class="space-y-4 text-slate-500 text-sm">
                            <li><a class="hover:text-accent transition-colors" href="{{ route('terms') }}">Terms of Service</a></li>
                            <li><a class="hover:text-accent transition-colors" href="{{ route('privacy') }}">Privacy Policy</a></li>
                            <li><a class="hover:text-accent transition-colors" href="{{ route('terms') }}">Cookie Policy</a></li>
                            <li><a class="hover:text-accent transition-colors" href="{{ route('support') }}">Compliance</a></li>
                        </ul>
                    </div>
                </div>

                <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-4 text-slate-600 text-xs font-bold uppercase tracking-wider">
                    <p>© {{ now()->year }} 7th Trade Hub. All rights reserved.</p>
                    <div class="flex gap-6">
                        <a class="hover:text-white transition-colors" href="#">Twitter</a>
                        <a class="hover:text-white transition-colors" href="#">Telegram</a>
                        <a class="hover:text-white transition-colors" href="#">Discord</a>
                    </div>
                </div>
            </div>
        </footer>

        @RegisterServiceWorkerScript
    </body>
</html>

