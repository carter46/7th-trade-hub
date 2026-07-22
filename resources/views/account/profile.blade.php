@extends($layout)

@section('title', 'Profile')

@section('content')
<x-layout.page title="My Account" subtitle="Manage your profile and account settings." width="full">
    @include('account.partials.navigation')

    <div id="dashboard-tab-panel" class="mt-4 space-y-4">
        @include('account.partials.panel-profile')
    </div>
</x-layout.page>
@endsection
