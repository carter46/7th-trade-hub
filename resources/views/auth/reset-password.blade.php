<x-layouts.auth>
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-[480px] glass-card rounded-xl shadow-2xl border border-slate-700/50 p-8 lg:p-10">
            <div class="mb-8">
                <h1 class="text-3xl font-black leading-tight tracking-tight text-white mb-2">Reset Password</h1>
                <p class="text-slate-400 text-base">Create a new secure password for your account to regain access.</p>
            </div>

            <form class="space-y-6" action="{{ route('password.store') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1.5" for="email">Email</label>
                    <input class="w-full pl-4 pr-4 py-3.5 bg-slate-800/50 border border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all placeholder:text-slate-500 text-white @error('email') border-red-500 @enderror" id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus placeholder="name@company.com"/>
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-300 ml-1" for="password">New Password</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-4 text-slate-400">lock</span>
                        <input class="w-full pl-12 pr-12 py-3.5 bg-slate-800/50 border border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all placeholder:text-slate-500 text-white @error('password') border-red-500 @enderror" id="password" name="password" type="password" placeholder="Min. 8 characters" required/>
                    </div>
                    @error('password')
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-300 ml-1" for="password_confirmation">Confirm New Password</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-4 text-slate-400">lock_reset</span>
                        <input class="w-full pl-12 pr-12 py-3.5 bg-slate-800/50 border border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all placeholder:text-slate-500 text-white" id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat your new password" required/>
                    </div>
                </div>

                <div class="bg-primary/10 border border-primary/20 rounded-lg p-4 space-y-2">
                    <p class="text-xs font-bold text-primary uppercase tracking-wider">Security Requirements:</p>
                    <ul class="text-xs space-y-1.5 text-slate-400">
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[14px] text-primary">check_circle</span>
                            At least 8 characters long
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[14px] text-primary">check_circle</span>
                            One uppercase & one number
                        </li>
                    </ul>
                </div>

                <button class="w-full bg-accent hover:bg-green-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-900/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2" type="submit">
                    Reset Password
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-700 text-center">
                <a class="text-sm font-medium text-slate-400 hover:text-primary transition-colors inline-flex items-center gap-1" href="{{ route('login') }}">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Back to Login
                </a>
            </div>
        </div>
    </main>
</x-layouts.auth>
