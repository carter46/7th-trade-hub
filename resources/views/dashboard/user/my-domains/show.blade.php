@extends('layouts.dashboard-user')

@section('title', 'Manage '.$registration->fqdn)

@section('content')
@php
    $ns = $registration->nameserverList();
    $defaults = $platformDefaultNameservers ?? [];
@endphp
<x-layout.page
    :title="$registration->fqdn"
    subtitle="Manage domain registration and nameservers."
    width="default"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['My Domains', route('dashboard.my-domains')],
        [$registration->fqdn, null],
    ]"
>
    @if(session('status'))
        <x-dashboard.alert type="success">{{ session('status') }}</x-dashboard.alert>
    @endif
    @if(session('error'))
        <x-dashboard.alert type="danger">{{ session('error') }}</x-dashboard.alert>
    @endif

    <x-dashboard.card class="space-y-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Status</p>
            <div class="mt-1 flex items-center gap-2">
                <x-dashboard.badge :status="$registration->status" />
                @if($registration->registered_at)
                    <span class="text-sm text-text-secondary">Registered {{ $registration->registered_at->format('j M Y') }}</span>
                @endif
            </div>
        </div>

        @if($registration->order)
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Order</p>
                <p class="text-sm text-text-primary">{{ $registration->order->reference }}</p>
            </div>
        @endif

        @if($registration->error_message && ! $registration->isRegistered())
            <x-dashboard.alert type="warning">{{ $registration->error_message }}</x-dashboard.alert>
        @endif
    </x-dashboard.card>

    <x-dashboard.card class="mt-5 space-y-4">
        <div>
            <h2 class="text-lg font-semibold text-text-primary">Nameservers</h2>
            <p class="mt-1 text-sm text-text-secondary">
                These are the current nameservers for this domain. Changes may take 24–48 hours to propagate globally.
            </p>
        </div>

        @if($ns !== [])
            <dl class="space-y-2">
                @foreach($ns as $index => $host)
                    <div class="flex gap-3 text-sm">
                        <dt class="w-12 shrink-0 font-medium text-text-muted">NS{{ $index + 1 }}</dt>
                        <dd class="text-text-primary">{{ $host }}</dd>
                    </div>
                @endforeach
            </dl>
        @else
            <p class="text-sm text-text-muted">Nameserver details are not synced yet. Use Refresh from Registrar if this domain is registered.</p>
        @endif

        @if($canManageNameservers)
            <div class="flex flex-wrap gap-2 border-t border-border-default pt-4">
                <x-dashboard.button :href="route('dashboard.my-domains.show', ['registration' => $registration, 'change' => 1])" variant="secondary" size="sm">
                    Change Nameservers
                </x-dashboard.button>
                @if($defaults !== [])
                    <form method="POST" action="{{ route('dashboard.my-domains.nameservers.defaults', $registration) }}" class="inline">
                        @csrf
                        <x-dashboard.button type="submit" variant="secondary" size="sm">Use Platform Defaults</x-dashboard.button>
                    </form>
                @endif
                <form method="POST" action="{{ route('dashboard.my-domains.nameservers.sync', $registration) }}" class="inline">
                    @csrf
                    <x-dashboard.button type="submit" variant="secondary" size="sm">Refresh from Registrar</x-dashboard.button>
                </form>
            </div>
        @else
            <p class="text-sm text-text-muted border-t border-border-default pt-4">
                Nameservers can be changed once domain registration completes successfully.
            </p>
        @endif
    </x-dashboard.card>

    @if($showChangeForm && $canManageNameservers)
        <x-dashboard.card class="mt-5 space-y-4">
            <h2 class="text-lg font-semibold text-text-primary">Change Nameservers</h2>
            <form method="POST" action="{{ route('dashboard.my-domains.nameservers.update', $registration) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @for($i = 1; $i <= 4; $i++)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-text-secondary">
                            NS{{ $i }} @if($i <= 2)<span class="text-danger">*</span>@else<span class="text-text-muted">(optional)</span>@endif
                        </label>
                        <input
                            type="text"
                            name="nameserver_{{ $i }}"
                            value="{{ old('nameserver_'.$i, $ns[$i - 1] ?? '') }}"
                            placeholder="ns{{ $i }}.example.com"
                            @if($i <= 2) required @endif
                            class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm"
                        >
                    </div>
                @endfor
                <div class="flex flex-wrap gap-2">
                    <x-dashboard.button type="submit">Save Nameservers</x-dashboard.button>
                    <x-dashboard.button :href="route('dashboard.my-domains.show', $registration)" variant="secondary">Cancel</x-dashboard.button>
                </div>
            </form>
        </x-dashboard.card>
    @endif
</x-layout.page>
@endsection
