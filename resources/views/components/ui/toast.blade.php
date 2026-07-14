@php
    $statusLabels = [
        'verification-otp-sent' => 'A new verification code has been sent to your email.',
        'verification-link-sent' => 'A new verification link has been sent to your email address.',
        'profile-updated' => 'Profile updated.',
        'password-updated' => 'Password updated.',
    ];
    $toasts = [];
    foreach (['status' => 'success', 'success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $key => $type) {
        if (session()->has($key)) {
            $raw = (string) session($key);
            $toasts[] = ['type' => $type, 'message' => $statusLabels[$raw] ?? $raw];
        }
    }
@endphp

<div
    x-data="toastStore({{ \Illuminate\Support\Js::from($toasts) }})"
    class="pointer-events-none fixed top-4 right-4 z-[100] flex w-full max-w-sm flex-col gap-2"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            class="pointer-events-auto flex items-start gap-3 rounded-xl border px-4 py-3 shadow-lg min-h-[56px]"
            :class="{
                'bg-success/15 border-success/40 text-green-100': toast.type === 'success',
                'bg-danger/15 border-danger/40 text-red-100': toast.type === 'error',
                'bg-warning/15 border-warning/40 text-amber-100': toast.type === 'warning',
                'bg-blue-500/15 border-blue-500/40 text-blue-100': toast.type === 'info',
            }"
            x-show="true"
            x-transition
        >
            <p class="flex-1 text-sm" x-text="toast.message"></p>
            <button type="button" class="text-current/70 hover:text-current shrink-0" @click="dismiss(toast.id)" aria-label="Dismiss">
                <x-ui.icon name="x" class="w-4 h-4" />
            </button>
        </div>
    </template>
</div>
