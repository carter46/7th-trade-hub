@extends($layout)

@section('title', 'Preferences')

@section('content')
<x-layout.page title="Preferences" subtitle="Choose how your dashboard looks and feels." width="full">
    @include('account.partials.navigation')

    <div id="dashboard-tab-panel" class="mt-4 space-y-4">
        @include('account.partials.panel-preferences')
    </div>
</x-layout.page>
@endsection
