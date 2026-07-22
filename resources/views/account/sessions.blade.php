@extends($layout)

@section('title', 'Sessions')

@section('content')
<x-layout.page title="Sessions" subtitle="Review devices signed in to your account." width="full">
    @include('account.partials.navigation')

    <div id="dashboard-tab-panel" class="mt-4 space-y-3">
        @include('account.partials.panel-sessions')
    </div>
</x-layout.page>
@endsection
