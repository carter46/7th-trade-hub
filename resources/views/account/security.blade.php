@extends($layout)

@section('title', 'Security')

@section('content')
<x-layout.page title="Security" subtitle="Update your password and protect your account." width="full">
    @include('account.partials.navigation')

    <div id="dashboard-tab-panel" class="mt-4 space-y-4">
        @include('account.partials.panel-security')
    </div>
</x-layout.page>
@endsection
