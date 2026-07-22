<x-dashboard.card>
    @include('profile.partials.update-password-form')
</x-dashboard.card>

@unless ($user->hasRole('admin'))
    <x-dashboard.card>
        @include('profile.partials.delete-user-form', [
            'profileDestroyRoute' => $prefix.'.account.destroy',
        ])
    </x-dashboard.card>
@endunless
