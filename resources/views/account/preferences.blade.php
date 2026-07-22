@extends($layout)

@section('title', 'Preferences')

@section('content')
<x-layout.page title="Preferences" subtitle="Choose how your dashboard looks and feels." width="full">
    @include('account.partials.navigation')

    <x-dashboard.card>
        <div class="space-y-5">
            <div>
                <h2 class="text-lg font-semibold text-text-primary">Theme preference</h2>
                <p class="mt-1 text-sm text-text-secondary">
                    Select a light or dark dashboard, or follow your device setting.
                </p>
            </div>
            <x-dashboard.theme-switcher />
        </div>
    </x-dashboard.card>
</x-layout.page>
@endsection
