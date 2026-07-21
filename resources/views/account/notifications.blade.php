@extends($layout)

@section('title', 'Notification Preferences')

@section('content')
<x-layout.page title="Notifications" subtitle="Review notification activity and read status." width="content-md">
    @include('account.partials.navigation')

    <x-dashboard.card>
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-text-primary">Notification center</h2>
                <p class="mt-1 text-sm text-text-secondary">
                    You have {{ $unreadCount }} unread {{ \Illuminate\Support\Str::plural('notification', $unreadCount) }}.
                </p>
            </div>
            <x-dashboard.button :href="route($prefix === 'admin' ? 'admin.notifications' : 'dashboard.notifications')" variant="secondary">
                View notifications
            </x-dashboard.button>
        </div>
    </x-dashboard.card>

    <x-dashboard.alert type="info">
        Delivery-channel preferences will appear here as additional channels become available.
    </x-dashboard.alert>
</x-layout.page>
@endsection
