<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-text-primary">
            {{ __('Delete Account') }}
        </h2>
        <p class="mt-1 text-sm text-text-secondary">
            {{ __('Once your account is deleted, your personal information is permanently removed. Business and financial records are retained in anonymized form. Before deleting, download any data you wish to keep.') }}
        </p>
    </header>

    <x-dashboard.button
        type="button"
        variant="danger"
        x-data
        @click="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        {{ __('Delete Account') }}
    </x-dashboard.button>

    <x-dashboard.modal
        name="confirm-user-deletion"
        title="{{ __('Are you sure you want to delete your account?') }}"
        variant="danger"
        confirm-label="{{ __('Delete Account') }}"
        form-action="{{ route($profileDestroyRoute ?? 'profile.destroy') }}"
        method="DELETE"
        x-init="{{ $errors->userDeletion->isNotEmpty() ? 'open = true' : '' }}"
    >
        <p class="text-text-secondary">
            {{ __('This permanently removes your personal information and signs you out. Please enter your password to confirm.') }}
        </p>

        <x-slot:form>
            <div class="mb-4">
                <x-dashboard.input
                    label="{{ __('Password') }}"
                    name="password"
                    type="password"
                    id="password"
                    placeholder="{{ __('Password') }}"
                    :error="$errors->userDeletion->first('password')"
                />
            </div>
        </x-slot:form>
    </x-dashboard.modal>
</section>
