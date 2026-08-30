@extends('layouts.dashboard-admin')

@section('title', $integration->name)

@section('content')
<x-layout.page
    title="{{ $integration->name }}"
    subtitle="{{ $integration->product?->title }}"
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Demo Site Integrate', route('admin.site-integrations')],
        [$integration->name, null],
    ]"
>
    <x-slot:actions>
        <form method="POST" action="{{ route('admin.site-integrations.check', $integration) }}">
            @csrf
            <x-dashboard.button type="submit" variant="secondary" size="sm">Check connection</x-dashboard.button>
        </form>
        <form method="POST" action="{{ route('admin.site-integrations.rotate', $integration) }}" onsubmit="return confirm('Rotate credentials? The site must update its config.');">
            @csrf
            <x-dashboard.button type="submit" variant="secondary" size="sm">Rotate keys</x-dashboard.button>
        </form>
    </x-slot:actions>

    @if ($freshCredentials ?? null)
        <x-dashboard.card class="mb-6 border-primary/40 bg-primary/5">
            <h3 class="text-sm font-semibold text-text-primary">Credentials (copy now — secret shown once)</h3>
            <dl class="mt-3 space-y-2 font-mono text-xs">
                <div><dt class="text-text-muted">Integration ID</dt><dd>{{ $freshCredentials['integration_id'] }}</dd></div>
                <div><dt class="text-text-muted">Client ID</dt><dd>{{ $freshCredentials['client_id'] }}</dd></div>
                <div><dt class="text-text-muted">Client Secret</dt><dd>{{ $freshCredentials['client_secret'] }}</dd></div>
                <div><dt class="text-text-muted">Webhook Secret</dt><dd>{{ $freshCredentials['webhook_secret'] }}</dd></div>
                <div><dt class="text-text-muted">Webhook URL</dt><dd>{{ $freshCredentials['webhook_url'] }}</dd></div>
            </dl>
        </x-dashboard.card>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-dashboard.card>
            <form method="POST" action="{{ route('admin.site-integrations.update', $integration) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-dashboard.input name="name" label="Name" :value="old('name', $integration->name)" required />
                <x-dashboard.input name="base_url" label="Base URL" type="url" :value="old('base_url', $integration->base_url)" required />
                <x-dashboard.input name="demo_user_email" label="Demo user email" type="email" :value="old('demo_user_email', $integration->demo_user_email)" />
                <x-dashboard.input name="demo_admin_email" label="Demo admin email" type="email" :value="old('demo_admin_email', $integration->demo_admin_email)" />
                <div>
                    <label class="mb-1 block text-sm font-medium">Status</label>
                    <x-dashboard.select name="status">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $integration->status->value) === $status->value)>{{ ucfirst($status->value) }}</option>
                        @endforeach
                    </x-dashboard.select>
                </div>
                <fieldset class="space-y-2">
                    <legend class="text-sm font-medium">Capabilities</legend>
                    @foreach (\App\Models\SiteIntegration::defaultCapabilities() as $cap)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="capabilities[]" value="{{ $cap }}" class="rounded border-border-default"
                                @checked(in_array($cap, old('capabilities', $integration->capabilities ?? []), true))>
                            {{ $cap }}
                        </label>
                    @endforeach
                </fieldset>
                <p class="text-xs text-text-muted">Client ID: <span class="font-mono">{{ $integration->client_id }}</span></p>
                <p class="text-xs text-text-muted">Integration ID: <span class="font-mono">{{ $integration->integration_id }}</span></p>
                <p class="text-xs text-text-muted">Connection: {{ $integration->connection_status ?? 'unchecked' }} @if($integration->last_error) — {{ $integration->last_error }} @endif</p>
                <x-dashboard.button type="submit">Save</x-dashboard.button>
            </form>
        </x-dashboard.card>

        <x-dashboard.card>
            <h3 class="mb-3 text-sm font-semibold">Connection logs</h3>
            <div class="space-y-2">
                @forelse ($logs as $log)
                    <div class="rounded-lg border border-border-default px-3 py-2 text-xs">
                        <div class="flex justify-between gap-2">
                            <span class="{{ $log->ok ? 'text-emerald-600' : 'text-red-600' }}">{{ $log->ok ? 'OK' : 'Fail' }}</span>
                            <span class="text-text-muted">{{ $log->created_at->format('j M Y H:i') }}</span>
                        </div>
                        <p class="mt-1 text-text-secondary">{{ $log->message }}</p>
                    </div>
                @empty
                    <p class="text-sm text-text-muted">No checks yet.</p>
                @endforelse
            </div>
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
