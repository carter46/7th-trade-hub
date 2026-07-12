@extends('layouts.marketing')

@section('title', 'Crypto Exchange | 7th Trade Hub')

@section('content')
<section class="relative py-16 lg:py-24 overflow-hidden">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10 opacity-20 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-600 rounded-full blur-[120px]"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <div class="flex flex-col gap-6">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 border border-primary/20 text-primary text-xs font-bold uppercase tracking-wider w-fit">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                </span>
                Fastest Payouts in the Market
            </span>
            <h1 class="font-display text-4xl lg:text-6xl font-bold leading-tight">
                Secure <span class="text-primary">Crypto-to-Cash</span> Exchange
            </h1>
            <p class="text-lg text-slate-400 max-w-lg leading-relaxed">
                Convert your digital assets into local currency in minutes. Experience institutional-grade security with the best market rates, guaranteed.
            </p>
            <div class="flex flex-wrap gap-4 pt-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">verified</span>
                    <span class="text-sm font-medium">Fully Licensed</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">speed</span>
                    <span class="text-sm font-medium">Instant Processing</span>
                </div>
            </div>
        </div>
        <div class="glassmorphism p-8 rounded-2xl shadow-2xl">
            <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">calculate</span>
                Exchange Calculator
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-2 block tracking-widest">You Sell</label>
                    <div class="relative">
                        <input class="w-full h-14 bg-slate-800/50 border border-slate-700 rounded-xl px-4 text-xl font-bold text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all" type="number" value="0.5" placeholder="0"/>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                            <select class="bg-slate-700 border-none rounded-lg font-bold py-2 pl-3 pr-8 text-white focus:ring-0 text-sm">
                                <option>BTC</option>
                                <option>ETH</option>
                                <option>USDT</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center -my-2 relative z-10">
                    <div class="bg-primary p-2 rounded-full shadow-lg shadow-primary/30">
                        <span class="material-symbols-outlined text-white">south</span>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-2 block tracking-widest">You Receive (Est.)</label>
                    <div class="relative">
                        <input class="w-full h-14 bg-slate-800/50 border border-slate-700 rounded-xl px-4 text-xl font-bold text-primary outline-none" readonly type="text" value="21,408.75"/>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">USD</span>
                    </div>
                </div>
                <button class="w-full h-14 bg-primary hover:bg-accent text-white rounded-xl font-bold text-lg shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 mt-4">
                    Start My Exchange
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </div>
        </div>
    </div>
</section>
<section class="py-20 bg-slate-900/50" id="how-it-works">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="font-display text-3xl font-bold mb-4">How it Works</h2>
            <p class="text-slate-400 max-w-xl mx-auto">Three simple steps to convert your crypto into cash.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="flex flex-col items-center text-center">
                <div class="size-16 rounded-2xl bg-slate-800 flex items-center justify-center mb-6 border border-slate-700">
                    <span class="material-symbols-outlined text-3xl text-primary">account_balance_wallet</span>
                </div>
                <h4 class="text-xl font-bold mb-3">1. Select Crypto</h4>
                <p class="text-slate-400 text-sm">Choose from BTC, ETH, or USDT and enter the amount.</p>
            </div>
            <div class="flex flex-col items-center text-center">
                <div class="size-16 rounded-2xl bg-slate-800 flex items-center justify-center mb-6 border border-slate-700">
                    <span class="material-symbols-outlined text-3xl text-primary">security</span>
                </div>
                <h4 class="text-xl font-bold mb-3">2. Create Request</h4>
                <p class="text-slate-400 text-sm">Confirm the rate and provide your payout details.</p>
            </div>
            <div class="flex flex-col items-center text-center">
                <div class="size-16 rounded-2xl bg-slate-800 flex items-center justify-center mb-6 border border-slate-700">
                    <span class="material-symbols-outlined text-3xl text-primary">payments</span>
                </div>
                <h4 class="text-xl font-bold mb-3">3. Get Paid</h4>
                <p class="text-slate-400 text-sm">Send crypto and receive cash in your account.</p>
            </div>
        </div>
    </div>
</section>
@endsection
