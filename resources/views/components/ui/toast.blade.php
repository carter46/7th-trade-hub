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
            class="pointer-events-auto flex items-start gap-3 rounded-xl border px-4 py-3 shadow-panel min-h-[56px] bg-elevated text-text-primary"
            :class="{
                'border-success/40': toast.type === 'success',
                'border-danger/40': toast.type === 'error',
                'border-warning/40': toast.type === 'warning',
                'border-primary/40': toast.type === 'info',
            }"
            x-show="true"
            x-transition
        >
            <p class="flex-1 text-sm" x-text="toast.message"></p>
            <button type="button" class="inline-flex min-h-11 min-w-11 items-center justify-center text-text-muted hover:text-text-primary shrink-0 -mr-2 -mt-1" @click="dismiss(toast.id)" aria-label="Dismiss">
                <x-ui.icon name="x" class="w-4 h-4" />
            </button>
        </div>
    </template>
</div>
