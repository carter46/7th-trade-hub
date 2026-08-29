@extends('layouts.marketing')

@section('title', 'Marketplace — Coming Soon')

@section('content')
    <section class="flex min-h-[calc(100dvh-5rem)] items-center justify-center px-5 sm:px-6 py-20">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-accent mb-6">Marketplace</p>
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold font-display text-white leading-none mb-8">
                Coming Soon
            </h1>
            <p class="text-slate-400 text-base sm:text-lg max-w-md mx-auto mb-10 leading-relaxed">
                We are building a place for sellers to list digital products. Register or sign in to get ready.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                @guest
                    <a
                        href="{{ route('register') }}"
                        class="w-full sm:w-auto px-8 py-4 rounded-xl bg-primary hover:bg-accent text-white font-bold text-sm sm:text-base transition-colors shadow-lg"
                    >
                        Register to become a seller
                    </a>
                    <a
                        href="{{ route('login') }}"
                        class="w-full sm:w-auto px-8 py-4 rounded-xl glassmorphism hover:bg-white/10 text-white font-bold text-sm sm:text-base border border-white/20 transition-colors"
                    >
                        Sign in
                    </a>
                @else
                    <a
                        href="{{ route('dashboard') }}"
                        class="w-full sm:w-auto px-8 py-4 rounded-xl bg-primary hover:bg-accent text-white font-bold text-sm sm:text-base transition-colors shadow-lg"
                    >
                        Go to dashboard
                    </a>
                @endguest
            </div>
        </div>
    </section>
@endsection
