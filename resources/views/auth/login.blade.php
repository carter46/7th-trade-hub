<x-layouts.auth>
    <main class="w-full max-w-md mx-auto" data-purpose="auth-wrapper">
        <div class="text-center mb-8" data-purpose="branding">
            <h1 class="text-3xl font-bold text-white tracking-tight">7th Trade Hub</h1>
            <p class="text-slate-400 mt-2">Connecting markets, empowering traders.</p>
        </div>

        {{-- Login Section --}}
        <section class="glass-card rounded-2xl p-8 shadow-2xl auth-transition {{ (request()->get('view') === 'signup' || ($showSignup ?? false)) ? 'hidden-section' : '' }}" data-purpose="login-form-container" id="login-section">
            <header class="mb-8">
                <h2 class="text-2xl font-semibold text-white">Welcome Back</h2>
                <p class="text-slate-400 text-sm">Please enter your details to sign in.</p>
            </header>
            @if (session('status'))
                <p class="mb-4 text-sm text-green-400">{{ session('status') }}</p>
            @endif
            <form action="{{ route('login') }}" class="space-y-5" method="POST">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5" for="login-email">Email</label>
                    <input class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-accent focus:border-transparent transition-all outline-none @error('email') border-red-500 @enderror" id="login-email" name="email" placeholder="johndoe@example.com" type="email" value="{{ old('email') }}" required autofocus/>
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5" for="login-password">Password</label>
                    <input class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-accent focus:border-transparent transition-all outline-none @error('password') border-red-500 @enderror" id="login-password" name="password" placeholder="••••••••" type="password" required/>
                    @error('password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center cursor-pointer group">
                        <input class="rounded border-slate-700 bg-slate-800 text-accent focus:ring-accent focus:ring-offset-slate-900" name="remember" type="checkbox"/>
                        <span class="ml-2 text-slate-400 group-hover:text-slate-300 transition-colors">Remember me</span>
                    </label>
                    <a class="text-accent hover:text-green-400 font-medium transition-colors" href="{{ route('password.request') }}">Forgot Password?</a>
                </div>
                <button class="w-full bg-accent hover:bg-green-700 text-white font-semibold py-3 rounded-lg shadow-lg shadow-green-900/20 transition-all transform hover:-translate-y-0.5 active:scale-[0.98]" type="submit">Login</button>
            </form>
            <footer class="mt-8 pt-6 border-t border-slate-700/50 text-center">
                <p class="text-slate-400 text-sm">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-accent hover:text-green-400 font-semibold transition-colors">Create Account</a>
                </p>
            </footer>
        </section>

        {{-- Sign Up Section --}}
        <section class="glass-card rounded-2xl p-8 shadow-2xl auth-transition {{ (request()->get('view') === 'signup' || ($showSignup ?? false)) ? '' : 'hidden-section' }}" data-purpose="signup-form-container" id="signup-section">
            <header class="mb-6">
                <h2 class="text-2xl font-semibold text-white">Join the Hub</h2>
                <p class="text-slate-400 text-sm">Get started with your free account today.</p>
            </header>
            <form action="{{ route('register') }}" class="space-y-4" method="POST">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5" for="signup-name">Full Name</label>
                    <input class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-accent focus:border-transparent outline-none @error('name') border-red-500 @enderror" id="signup-name" name="name" placeholder="John Doe" type="text" value="{{ old('name') }}" required/>
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5" for="signup-email">Email</label>
                        <input class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-accent focus:border-transparent outline-none @error('email') border-red-500 @enderror" id="signup-email" name="email" placeholder="john@example.com" type="email" value="{{ old('email') }}" required/>
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5" for="signup-username">Username</label>
                        <input class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-accent focus:border-transparent outline-none @error('username') border-red-500 @enderror" id="signup-username" name="username" placeholder="johndoe7" type="text" value="{{ old('username') }}" required/>
                        @error('username')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5" for="signup-password">Password</label>
                    <input class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-accent focus:border-transparent outline-none @error('password') border-red-500 @enderror" id="signup-password" name="password" placeholder="••••••••" type="password" required/>
                    @error('password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5" for="signup-password_confirmation">Confirm Password</label>
                    <input class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:ring-2 focus:ring-accent focus:border-transparent outline-none" id="signup-password_confirmation" name="password_confirmation" placeholder="••••••••" type="password" required/>
                </div>
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input class="rounded border-slate-700 bg-slate-800 text-accent focus:ring-accent focus:ring-offset-slate-900 h-4 w-4" id="terms" name="terms" type="checkbox" required/>
                    </div>
                    <div class="ml-3 text-sm">
                        <label class="text-slate-400" for="terms">
                            I agree to the <a class="text-accent hover:underline" href="{{ route('terms') }}">Terms of Service</a> and <a class="text-accent hover:underline" href="{{ route('privacy') }}">Privacy Policy</a>.
                        </label>
                    </div>
                </div>
                <button class="w-full bg-accent hover:bg-green-700 text-white font-semibold py-3 rounded-lg shadow-lg shadow-green-900/20 transition-all transform hover:-translate-y-0.5 active:scale-[0.98]" type="submit">Create Account</button>
            </form>
            <footer class="mt-6 pt-6 border-t border-slate-700/50 text-center">
                <p class="text-slate-400 text-sm">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-accent hover:text-green-400 font-semibold transition-colors">Login Here</a>
                </p>
            </footer>
        </section>
    </main>
</x-layouts.auth>
