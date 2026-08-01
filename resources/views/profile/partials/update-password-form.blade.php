<section>
    @php
        $passwordIsSet = $passwordIsSet ?? ($user->hasPasswordSet() ?? true);
    @endphp
    <header class="mb-6">
        <h2 class="text-lg font-medium text-text-primary">
            {{ $passwordIsSet ? __('Update Password') : __('Set Password') }}
        </h2>
        <p class="mt-1 text-sm text-text-secondary">
            @if ($passwordIsSet)
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            @else
                {{ __('You signed in with Google. Set a password to also sign in with email, disconnect Google later, or delete your account.') }}
            @endif
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        @method('put')

        @if ($passwordIsSet)
            <x-dashboard.input
                label="{{ __('Current Password') }}"
                name="current_password"
                type="password"
                id="update_password_current_password"
                autocomplete="current-password"
                :error="$errors->updatePassword->first('current_password')"
            />
        @endif

        <x-dashboard.input
            label="{{ $passwordIsSet ? __('New Password') : __('Password') }}"
            name="password"
            type="password"
            id="update_password_password"
            autocomplete="new-password"
            :error="$errors->updatePassword->first('password')"
        />

        <x-dashboard.input
            label="{{ __('Confirm Password') }}"
            name="password_confirmation"
            type="password"
            id="update_password_password_confirmation"
            autocomplete="new-password"
            :error="$errors->updatePassword->first('password_confirmation')"
        />

        <x-dashboard.button type="submit" x-bind:loading="submitting">{{ __('Save') }}</x-dashboard.button>
    </form>
</section>
