<x-layouts.auth>
    <main class="w-full max-w-auth mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-text-primary tracking-tight">7th Trade Hub</h1>
            <p class="text-text-secondary mt-2">Create your free account.</p>
        </div>

        <x-ui.card class="p-8">
            <header class="mb-6">
                <h2 class="text-2xl font-semibold text-text-primary">Join the Hub</h2>
                <p class="text-text-secondary text-sm">Get started with your free account today.</p>
            </header>

            <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <x-ui.input label="Name" name="name" type="text" id="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-ui.input label="Email" name="email" type="email" id="email" :value="old('email')" required autocomplete="username" />
                <x-ui.input label="Password" name="password" type="password" id="password" required autocomplete="new-password" />
                <x-ui.input label="Confirm Password" name="password_confirmation" type="password" id="password_confirmation" required autocomplete="new-password" />

                <div class="flex items-center justify-between gap-4 pt-2">
                    <a class="text-sm text-text-secondary hover:text-accent transition-colors" href="{{ route('login') }}">
                        Already registered?
                    </a>
                    <x-ui.button type="submit" x-bind:loading="submitting">Register</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </main>
</x-layouts.auth>
