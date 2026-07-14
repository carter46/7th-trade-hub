<x-layouts.auth>
    <main class="w-full max-w-auth mx-auto">
        <x-ui.card class="p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-text-primary">Confirm password</h1>
                <p class="mt-2 text-sm text-text-secondary">
                    This is a secure area of the application. Please confirm your password before continuing.
                </p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <x-ui.input
                    label="Password"
                    name="password"
                    type="password"
                    id="password"
                    required
                    autocomplete="current-password"
                    autofocus
                />

                <div class="flex justify-end">
                    <x-ui.button type="submit" x-bind:loading="submitting">Confirm</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </main>
</x-layouts.auth>
