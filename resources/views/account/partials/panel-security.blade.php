<x-dashboard.card>
    @include('profile.partials.update-password-form')
</x-dashboard.card>

<x-dashboard.card>
    <section class="space-y-4">
        <header>
            <h2 class="text-lg font-medium text-text-primary">{{ __('Connected Accounts') }}</h2>
            <p class="mt-1 text-sm text-text-secondary">{{ __('Link Google to sign in faster. You can disconnect only if another login method remains.') }}</p>
        </header>

        @if (session('status') === 'google-linked')
            <p class="text-sm text-success">{{ __('Google account connected.') }}</p>
        @elseif (session('status') === 'google-unlinked')
            <p class="text-sm text-success">{{ __('Google account disconnected.') }}</p>
        @elseif (session('status') === 'password-set')
            <p class="text-sm text-success">{{ __('Password set successfully.') }}</p>
        @endif

        @error('google')
            <p class="text-sm text-danger">{{ $message }}</p>
        @enderror

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-xl border border-border-subtle p-4">
            <div>
                <p class="font-medium text-text-primary">Google</p>
                @if ($googleLinked ?? false)
                    <p class="text-sm text-success">{{ __('Connected') }}</p>
                @else
                    <p class="text-sm text-text-secondary">{{ __('Not connected') }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if ($googleLinked ?? false)
                    @if ($canDisconnectGoogle ?? false)
                        <form method="POST" action="{{ route('auth.google.unlink') }}">
                            @csrf
                            @method('DELETE')
                            <x-dashboard.button type="submit" variant="secondary">{{ __('Disconnect') }}</x-dashboard.button>
                        </form>
                    @else
                        <p class="text-xs text-text-secondary max-w-xs text-right">{{ __('Set a password before disconnecting Google.') }}</p>
                    @endif
                @elseif (! empty(($googleIdentityConfig ?? [])['enabled']))
                    @include('partials.auth.google-gis', [
                        'mode' => 'button',
                        'surface' => 'link',
                        'buttonText' => 'continue_with',
                        'endpoint' => route('auth.google.link'),
                    ])
                @else
                    <p class="text-sm text-text-secondary">{{ __('Google Sign-In is not enabled.') }}</p>
                @endif
            </div>
        </div>
    </section>
</x-dashboard.card>

@unless ($user->hasRole('admin'))
    <x-dashboard.card>
        @if ($passwordIsSet ?? true)
            @include('profile.partials.delete-user-form', [
                'profileDestroyRoute' => $prefix.'.account.destroy',
            ])
        @else
            <section>
                <header>
                    <h2 class="text-lg font-medium text-text-primary">{{ __('Delete Account') }}</h2>
                    <p class="mt-1 text-sm text-text-secondary">{{ __('Set a password first so you can confirm account deletion securely.') }}</p>
                </header>
            </section>
        @endif
    </x-dashboard.card>
@endunless
