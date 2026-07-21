@extends($layout)

@section('title', 'Security')

@section('content')
<x-layout.page title="Security" subtitle="Update your password and protect your account." width="content-md">
    @include('account.partials.navigation')

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
</x-layout.page>
@endsection
