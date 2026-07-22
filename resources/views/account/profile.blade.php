@extends($layout)

@section('title', 'Profile')

@section('content')
<x-layout.page title="My Account" subtitle="Manage your profile and account settings." width="full">
    @include('account.partials.navigation')

    <x-dashboard.card>
        @include('profile.partials.update-profile-information-form', [
            'profileUpdateRoute' => $prefix.'.account.profile.update',
        ])
    </x-dashboard.card>
</x-layout.page>
@endsection
