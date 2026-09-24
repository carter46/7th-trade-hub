@extends('layouts.dashboard-user')

@section('title', 'Manage '.$registration->fqdn)

@section('content')
@php
    $ns = $registration->nameserverList();
    $defaults = $platformDefaultNameservers ?? [];
@endphp
<x-layout.page
    :title="$registration->fqdn"
    width="default"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['My Tools', route('dashboard.my-tools.domains')],
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

        @if($registration->isRejected() || $registration->isPendingReplacement())
            <div class="space-y-3 rounded-xl border border-danger/30 bg-danger/5 px-4 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-danger">Rejected domain</p>
                    <p class="mt-1 font-mono font-semibold text-text-primary">{{ $registration->rejectedFqdn() ?: $registration->fqdn }}</p>
                    @if($registration->rejectionReason())
                        <p class="mt-2 text-sm text-text-secondary">{{ $registration->rejectionReason() }}</p>
                    @endif
                </div>

                @if($registration->isPendingReplacement())
                    <div class="rounded-lg border border-primary/20 bg-primary/5 px-3 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Pending replacement</p>
                        <p class="mt-1 font-mono font-semibold text-text-primary">{{ $registration->fqdn }}</p>
                        <p class="mt-1 text-xs text-text-muted">Awaiting admin approval. You can submit a different domain below if needed.</p>
                    </div>
                @endif

                @if($registration->canRequestReplacement())
                    <div x-data="{ open: {{ $errors->has('fqdn') ? 'true' : 'false' }} }" class="space-y-3">
                        <x-dashboard.button type="button" variant="primary" size="sm" x-on:click="open = !open" x-text="open ? 'Hide replace form' : 'Replace domain'">
                            Replace domain
                        </x-dashboard.button>
                        <form
                            method="POST"
                            action="{{ route('dashboard.my-domains.replace', $registration) }}"
                            class="space-y-3"
                            x-show="open"
                            x-cloak
                        >
                            @csrf
                            <div>
                                <label for="fqdn" class="mb-1 block text-sm font-medium text-text-primary">New domain</label>
                                <input
                                    id="fqdn"
                                    type="text"
                                    name="fqdn"
                                    value="{{ old('fqdn') }}"
                                    required
                                    placeholder="example.com"
                                    class="w-full rounded-lg border border-border-default bg-elevated px-3 py-2 text-sm font-mono"
                                >
                                @error('fqdn')
                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <p class="text-xs text-text-muted">Free replacement — no extra charge. Same order price applies.</p>
                            <x-dashboard.button type="submit" variant="primary" size="sm">Submit replacement</x-dashboard.button>
                        </form>
                    </div>
                @endif
            </div>
        @elseif($registration->isPendingManual())
            <x-dashboard.alert type="info">
                Your domain purchase is queued for manual registration. We will update this page once an admin completes registration.
            </x-dashboard.alert>
        @elseif($registration->error_message && ! $registration->isRegistered())
            <x-dashboard.alert type="warning">{{ $registration->error_message }}</x-dashboard.alert>
        @endif
    </x-dashboard.card>

    <x-dashboard.card class="mt-5 space-y-4">
        <div>
            <h2 class="text-lg font-semibold text-text-primary">Nameservers</h2>
            <p class="mt-1 text-sm text-text-secondary">
                These are the current nameservers for this domain. Changes may take 24–48 hours to propagate globally.
            </p>
            @if($registration->isManualFulfillment() && $registration->isRegistered())
                <p class="mt-1 text-xs text-text-muted">
                    This domain was registered offline. Nameserver changes here are saved on your account; update the registrar panel separately if needed.
                </p>
            @endif
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
            <p class="text-sm text-text-muted">
                @if($registration->isPendingManual())
                    Nameservers will appear after manual registration is completed.
                @else
                    Nameserver details are not synced yet. Use Refresh from Registrar if this domain is registered.
                @endif
            </p>
        @endif

        @if($canManageNameservers)
            <div class="flex flex-wrap gap-2 border-t border-border-default pt-4">
                <x-dashboard.button :href="route('dashboard.my-domains.show', ['registration' => $registration, 'change' => 1])" variant="secondary" size="sm">
                    Change Nameservers
                </x-dashboard.button>
                @if($defaults !== [] && ! $registration->isManualFulfillment())
                    <form method="POST" action="{{ route('dashboard.my-domains.nameservers.defaults', $registration) }}" class="inline">
                        @csrf
                        <x-dashboard.button type="submit" variant="secondary" size="sm">Use Platform Defaults</x-dashboard.button>
                    </form>
                @endif
                @if(! $registration->isManualFulfillment())
                    <form method="POST" action="{{ route('dashboard.my-domains.nameservers.sync', $registration) }}" class="inline">
                        @csrf
                        <x-dashboard.button type="submit" variant="secondary" size="sm">Refresh from Registrar</x-dashboard.button>
                    </form>
                @endif
            </div>
        @else
            <p class="text-sm text-text-muted border-t border-border-default pt-4">
                @if($registration->isPendingManual())
                    Nameserver management unlocks after an admin marks this domain as registered.
                @else
                    Nameservers can be changed once domain registration completes successfully.
                @endif
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
