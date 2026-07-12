<x-layouts.auth>
    <main class="flex-grow flex items-center justify-center p-6">
        <div class="max-w-md w-full glass-card rounded-xl shadow-2xl border border-slate-700/50 overflow-hidden">
            <div class="p-8">
                <div class="flex justify-center mb-8">
                    <div class="size-20 bg-primary/20 rounded-full flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-4xl">mark_email_unread</span>
                    </div>
                </div>
                <div class="text-center space-y-2 mb-10">
                    <h1 class="text-3xl font-bold text-white">Verify your email</h1>
                    <p class="text-slate-400">
                        We've sent a 6-digit code to your email. Enter it below to activate your account.
                    </p>
                </div>

                @if (session('status') === 'verification-otp-sent')
                    <p class="mb-4 text-sm text-green-400 text-center">A new verification code has been sent to your email.</p>
                @endif

                <form action="{{ route('verification.verify') }}" method="POST" class="space-y-8" id="otp-form">
                    @csrf
                    <div class="flex justify-between gap-2 max-w-sm mx-auto" id="otp-inputs">
                        @php $oldOtp = str_split(old('otp', '')); @endphp
                        @for ($i = 0; $i < 6; $i++)
                            <input
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                class="w-12 h-14 text-center text-2xl font-bold rounded-lg border-2 border-slate-600 bg-slate-800/50 focus:border-primary focus:ring-0 transition-colors text-white @error('otp') border-red-500 @enderror"
                                maxlength="1"
                                type="text"
                                value="{{ $oldOtp[$i] ?? '' }}"
                                data-index="{{ $i }}"
                                aria-label="Digit {{ $i + 1 }}"
                            />
                        @endfor
                    </div>
                    <input type="hidden" name="otp" id="otp-combined" value="{{ old('otp') }}" />
                    @error('otp')
                        <p class="text-center text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    <div class="space-y-4">
                        <button class="w-full bg-accent hover:bg-green-600 text-white font-bold py-4 rounded-lg shadow-lg shadow-green-900/20 transition-all active:scale-[0.98]" type="submit">
                            Verify Email
                        </button>
                        <div class="text-center">
                            <p class="text-sm text-slate-400">
                                Didn't receive a code?
                                <form action="{{ route('verification.send') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-primary hover:underline font-semibold ml-1">Resend</button>
                                </form>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-slate-800/50 p-6 border-t border-slate-700">
                <div class="flex items-center gap-3 text-xs text-slate-400">
                    <span class="material-symbols-outlined text-sm">shield</span>
                    <p>Secured by 256-bit encryption. Your data is always protected.</p>
                </div>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('otp-form');
            const inputs = form.querySelectorAll('input[name="otp[]"]');
            const combined = document.getElementById('otp-combined');

            function updateCombined() {
                combined.value = Array.from(inputs).map(i => i.value).join('');
            }

            updateCombined();
            inputs.forEach((input, i) => {
                input.addEventListener('input', function() {
                    const v = this.value.replace(/\D/g, '');
                    this.value = v.slice(-1);
                    updateCombined();
                    if (v && i < inputs.length - 1) inputs[i + 1].focus();
                });
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value && i > 0) {
                        inputs[i - 1].focus();
                    }
                });
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasted = (e.clipboardData?.getData('text') || '').replace(/\D/g, '').slice(0, 6);
                    pasted.split('').forEach((char, j) => {
                        if (inputs[j]) inputs[j].value = char;
                    });
                    updateCombined();
                    if (inputs[pasted.length - 1]) inputs[pasted.length - 1].focus();
                });
            });

            form.addEventListener('submit', function() {
                updateCombined();
            });
        });
    </script>
</x-layouts.auth>
