<x-layouts.auth>
    <main class="w-full max-w-auth mx-auto">
        <x-ui.card class="overflow-hidden" :padding="false">
            <div class="p-8">
                <div class="flex justify-center mb-8">
                    <div class="size-20 bg-primary/20 rounded-full flex items-center justify-center text-primary">
                        <x-ui.icon name="notifications" class="w-10 h-10" />
                    </div>
                </div>
                <div class="text-center space-y-2 mb-10">
                    <h1 class="text-3xl font-bold text-text-primary">Verify your email</h1>
                    <p class="text-text-secondary">
                        We've sent a 6-digit code to your email. Enter it below to activate your account.
                    </p>
                </div>

                <form action="{{ route('verification.verify') }}" method="POST" class="space-y-8" id="otp-form" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <div class="flex justify-between gap-2 max-w-sm mx-auto" id="otp-inputs">
                        @php $oldOtp = str_split(old('otp', '')); @endphp
                        @for ($i = 0; $i < 6; $i++)
                            <input
                                inputmode="numeric"
                                autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                class="w-12 h-14 text-center text-2xl font-bold rounded-lg border-2 border-border-default bg-elevated/50 focus:border-primary focus:ring-0 transition-colors text-text-primary @error('otp') border-danger @enderror"
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
                        <x-ui.alert type="error">{{ $message }}</x-ui.alert>
                    @enderror
                    <div class="space-y-4">
                        <x-ui.button type="submit" class="w-full" size="lg" x-bind:loading="submitting">
                            Verify Email
                        </x-ui.button>
                        <div class="text-center text-sm text-text-secondary">
                            Didn't receive a code?
                        </div>
                    </div>
                </form>

                <form action="{{ route('verification.send') }}" method="POST" class="text-center mt-2">
                    @csrf
                    <x-ui.button type="submit" variant="link">Resend</x-ui.button>
                </form>
            </div>
            <div class="bg-elevated/50 p-6 border-t border-border-default">
                <div class="flex items-center gap-3 text-xs text-text-secondary">
                    <x-ui.icon name="lock" class="w-4 h-4 shrink-0" />
                    <p>Secured by 256-bit encryption. Your data is always protected.</p>
                </div>
            </div>
        </x-ui.card>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('otp-form');
            const inputs = form.querySelectorAll('#otp-inputs input');
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
                    if (inputs[Math.min(pasted.length, inputs.length) - 1]) {
                        inputs[Math.min(pasted.length, inputs.length) - 1].focus();
                    }
                });
            });

            form.addEventListener('submit', function() {
                updateCombined();
            });
        });
    </script>
</x-layouts.auth>
