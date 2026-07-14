<x-layouts.auth>
    <main class="w-full max-w-auth mx-auto">
        <x-ui.card class="p-8 lg:p-10">
            <div class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-text-primary mb-2">Reset Password</h1>
                <p class="text-text-secondary">Create a new secure password for your account to regain access.</p>
            </div>

            <form class="space-y-6" action="{{ route('password.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <x-ui.input
                    label="Email"
                    name="email"
                    type="email"
                    id="email"
                    :value="old('email', $request->email)"
                    required
                    autofocus
                    placeholder="name@company.com"
                />

                <x-ui.input
                    label="New Password"
                    name="password"
                    type="password"
                    id="password"
                    placeholder="Min. 8 characters"
                    required
                />

                <x-ui.input
                    label="Confirm New Password"
                    name="password_confirmation"
                    type="password"
                    id="password_confirmation"
                    placeholder="Repeat your new password"
                    required
                />

                <x-ui.alert type="info" title="Security requirements">
                    <ul class="space-y-1.5 mt-1">
                        <li class="flex items-center gap-2">
                            <x-ui.icon name="check" class="w-3.5 h-3.5 text-primary" />
                            At least 8 characters long
                        </li>
                        <li class="flex items-center gap-2">
                            <x-ui.icon name="check" class="w-3.5 h-3.5 text-primary" />
                            One uppercase &amp; one number
                        </li>
                    </ul>
                </x-ui.alert>

                <x-ui.button type="submit" class="w-full" size="lg" icon-right="chevron-right" x-bind:loading="submitting">
                    Reset Password
                </x-ui.button>
            </form>

            <div class="mt-8 pt-6 border-t border-border-default text-center">
                <a class="text-sm font-medium text-text-secondary hover:text-primary transition-colors inline-flex items-center gap-1" href="{{ route('login') }}">
                    <x-ui.icon name="chevron-right" class="w-4 h-4 rotate-180" />
                    Back to Login
                </a>
            </div>
        </x-ui.card>
    </main>
</x-layouts.auth>
