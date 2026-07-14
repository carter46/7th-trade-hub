<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-text-primary">
            {{ __('Delete Account') }}
        </h2>
        <p class="mt-1 text-sm text-text-secondary">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-ui.button
        type="button"
        variant="danger"
        x-data
        @click="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        {{ __('Delete Account') }}
    </x-ui.button>

    <x-ui.modal
        name="confirm-user-deletion"
        title="{{ __('Are you sure you want to delete your account?') }}"
        variant="danger"
        confirm-label="{{ __('Delete Account') }}"
        form-action="{{ route('profile.destroy') }}"
        method="DELETE"
        x-init="{{ $errors->userDeletion->isNotEmpty() ? 'open = true' : '' }}"
    >
        <p class="text-text-secondary">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
        </p>

        <x-slot:form>
            <div class="mb-4">
                <x-ui.input
                    label="{{ __('Password') }}"
                    name="password"
                    type="password"
                    id="password"
                    placeholder="{{ __('Password') }}"
                    :error="$errors->userDeletion->first('password')"
                />
            </div>
        </x-slot:form>
    </x-ui.modal>
</section>
