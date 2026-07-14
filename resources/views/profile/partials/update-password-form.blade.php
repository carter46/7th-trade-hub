<section>
    <header class="mb-6">
        <h2 class="text-lg font-medium text-text-primary">
            {{ __('Update Password') }}
        </h2>
        <p class="mt-1 text-sm text-text-secondary">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        @method('put')

        <x-ui.input
            label="{{ __('Current Password') }}"
            name="current_password"
            type="password"
            id="update_password_current_password"
            autocomplete="current-password"
            :error="$errors->updatePassword->first('current_password')"
        />

        <x-ui.input
            label="{{ __('New Password') }}"
            name="password"
            type="password"
            id="update_password_password"
            autocomplete="new-password"
            :error="$errors->updatePassword->first('password')"
        />

        <x-ui.input
            label="{{ __('Confirm Password') }}"
            name="password_confirmation"
            type="password"
            id="update_password_password_confirmation"
            autocomplete="new-password"
            :error="$errors->updatePassword->first('password_confirmation')"
        />

        <x-ui.button type="submit" x-bind:loading="submitting">{{ __('Save') }}</x-ui.button>
    </form>
</section>
