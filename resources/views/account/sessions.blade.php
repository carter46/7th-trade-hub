@extends($layout)

@section('title', 'Sessions')

@section('content')
<x-layout.page title="Sessions" subtitle="Review devices signed in to your account." width="full">
    @include('account.partials.navigation')

    <div class="space-y-3">
        @unless ($sessionsAvailable ?? true)
            <x-dashboard.alert type="info">
                Session listing is only available when the app uses database sessions.
            </x-dashboard.alert>
        @else
            @forelse ($sessions as $session)
                <x-dashboard.card>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="monitor" class="h-5 w-5 text-primary" />
                                <p class="font-semibold text-text-primary">
                                    {{ $session->is_current ? 'Current session' : 'Signed-in device' }}
                                </p>
                            </div>
                            <p class="mt-2 truncate text-sm text-text-secondary" title="{{ $session->user_agent }}">
                                {{ $session->user_agent ?: 'Unknown browser or device' }}
                            </p>
                            <p class="mt-1 text-xs text-text-secondary">
                                {{ $session->ip_address ?: 'Unknown IP' }} · Active {{ $session->last_active_at->diffForHumans() }}
                            </p>
                        </div>

                        @if ($session->is_current)
                            <x-dashboard.badge variant="success">This device</x-dashboard.badge>
                        @else
                            <form method="POST" action="{{ route($prefix.'.account.sessions.destroy', $session->id) }}">
                                @csrf
                                @method('DELETE')
                                <x-dashboard.button type="submit" variant="danger" size="sm">Revoke</x-dashboard.button>
                            </form>
                        @endif
                    </div>
                </x-dashboard.card>
            @empty
                <x-dashboard.alert type="info">No signed-in sessions were found for this account.</x-dashboard.alert>
            @endforelse
        @endunless
    </div>
</x-layout.page>
@endsection
