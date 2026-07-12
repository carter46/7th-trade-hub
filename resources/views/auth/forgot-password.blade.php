<x-layouts.auth>
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-[480px] space-y-8">
            <div class="flex flex-col gap-4 text-center md:text-left">
                <div class="inline-flex items-center justify-center md:justify-start">
                    <span class="material-symbols-outlined text-primary text-5xl">lock_reset</span>
                </div>
                <div class="space-y-2">
                    <h1 class="text-slate-100 text-4xl font-extrabold tracking-tight">Forgot Password</h1>
                    <p class="text-slate-400 text-lg leading-relaxed">
                        Enter your email to receive a password reset link. We'll help you get back into your trade hub.
                    </p>
                </div>
            </div>

            @if (session('status'))
                <p class="text-sm text-green-400">{{ session('status') }}</p>
            @endif
            @if ($errors->any())
                <p class="text-sm text-red-400">{{ $errors->first() }}</p>
            @endif

            <div class="glass-card p-8 rounded-xl shadow-xl border border-slate-700/50">
                <form class="space-y-6" action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <div class="flex flex-col gap-2">
                        <label class="text-slate-300 text-sm font-semibold uppercase tracking-wider" for="email">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-slate-400 text-xl">mail</span>
                            </div>
                            <input class="block w-full pl-11 pr-4 py-4 rounded-xl border border-slate-700 bg-slate-800/50 text-white placeholder-slate-500 focus:ring-2 focus:ring-primary focus:border-primary transition-all @error('email') border-red-500 @enderror" id="email" name="email" placeholder="name@company.com" type="email" value="{{ old('email') }}" required autofocus/>
                        </div>
                        @error('email')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <button class="w-full flex items-center justify-center gap-2 rounded-xl bg-accent hover:bg-green-600 text-white h-14 text-base font-bold transition-all shadow-lg shadow-green-900/20" type="submit">
                        <span>Send Reset Link</span>
                        <span class="material-symbols-outlined text-xl">arrow_forward</span>
                    </button>
                </form>
            </div>

            <div class="text-center">
                <a class="inline-flex items-center gap-2 text-accent hover:text-green-400 font-semibold transition-colors group" href="{{ route('login') }}">
                    <span class="material-symbols-outlined text-lg group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    <span>Back to Login</span>
                </a>
            </div>
            <div class="pt-8 flex items-center justify-center gap-2 text-slate-500 text-xs font-medium uppercase tracking-widest">
                <span class="material-symbols-outlined text-sm">verified_user</span>
                <span>Secured by 7th Trade encryption</span>
            </div>
        </div>
    </main>
</x-layouts.auth>
