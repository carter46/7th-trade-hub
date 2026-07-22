<x-dashboard.card>
    @include('profile.partials.update-profile-information-form', [
        'profileUpdateRoute' => $prefix.'.account.profile.update',
    ])
</x-dashboard.card>
