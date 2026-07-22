@extends($layout)

@section('title', 'Notification Preferences')

@section('content')
<x-layout.page title="Notifications" subtitle="Review notification activity and read status." width="full">
    @include('account.partials.navigation')

    <div id="dashboard-tab-panel" class="mt-4 space-y-4">
        @include('account.partials.panel-notifications')
    </div>
</x-layout.page>
@endsection
