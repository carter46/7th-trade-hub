@extends('layouts.dashboard-admin')

@section('title', 'Manage '.$connection->fqdn)

@section('content')
<x-layout.page
    :title="$connection->fqdn"
    width="default"
    :breadcrumb="[
        ['Users', route('admin.users')],
        [$user->name, route('admin.users.tools', $user)],
        ['Domain connection', null],
    ]"
>
    @if(session('status'))
        <x-dashboard.alert type="success">{{ session('status') }}</x-dashboard.alert>
    @endif
    @if(session('error'))
        <x-dashboard.alert type="danger">{{ session('error') }}</x-dashboard.alert>
    @endif

    <x-dashboard.card class="space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Connect existing domain</p>
                <h2 class="mt-1 text-xl font-semibold text-text-primary font-mono break-all">{{ $connection->fqdn }}</h2>
            </div>
            <x-dashboard.badge :status="$connection->verification_status" />
        </div>

        <dl class="grid gap-3 sm:grid-cols-2 text-sm">
            @if($connection->order)
                <div>
                    <dt class="text-text-muted">Order</dt>
                    <dd>
                        <a href="{{ route('admin.orders.show', $connection->order) }}" class="font-medium text-primary hover:underline">
                            {{ $connection->order->reference }}
                        </a>
                    </dd>
                </div>
            @endif
            <div>
                <dt class="text-text-muted">Verified at</dt>
                <dd class="font-medium text-text-primary">{{ $connection->verified_at?->format('j M Y H:i') ?? 'N/A' }}</dd>
            </div>
            @if($connection->userTool)
                <div>
                    <dt class="text-text-muted">Website tool</dt>
                    <dd>
                        <a href="{{ route('admin.users.tools.show', [$user, $connection->userTool]) }}" class="font-medium text-primary hover:underline">
                            Manage tool #{{ $connection->userTool->id }}
                        </a>
                    </dd>
                </div>
            @endif
        </dl>

        <x-dashboard.alert type="info">
            Connection domains use the DNS / verify workflow. You can replace the domain at any time, even after it is verified.
        </x-dashboard.alert>

        @if($connection->verification_status !== 'verified')
            <form method="POST" action="{{ route('admin.users.domains.connections.approve', [$user, $connection]) }}">
                @csrf
                <x-dashboard.button type="submit" variant="secondary">Approve domain connection</x-dashboard.button>
            </form>
        @endif
    </x-dashboard.card>

    <x-dashboard.card class="mt-6 space-y-4" x-data="{ open: {{ (old('fqdn') !== null || $errors->has('fqdn')) ? 'true' : 'false' }} }">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-text-primary">Replace domain</h3>
                <p class="mt-1 text-xs text-text-muted">
                    Change the domain whether it is pending or already verified. Updates the order line, the tool Website URL and admin login URL. The new domain starts as pending until its nameservers point to us, or you approve it. If the website was already integrated, re-install credentials on the new host and run Check connection.
                </p>
            </div>
            <x-dashboard.button type="button" variant="secondary" size="sm" x-on:click="open = !open" x-text="open ? 'Hide' : 'Replace domain'">
                Replace domain
            </x-dashboard.button>
        </div>
        <form
            method="POST"
            action="{{ route('admin.users.domains.connections.replace', [$user, $connection]) }}"
            class="space-y-3 border-t border-border-subtle pt-4"
            x-show="open"
            x-cloak
        >
            @csrf
            <x-dashboard.input
                name="fqdn"
                label="New domain"
                :value="old('fqdn')"
                placeholder="example.com"
                required
            />
            @error('fqdn')
                <p class="text-xs text-danger">{{ $message }}</p>
            @enderror
            <div>
                <label for="replace_note" class="mb-1 block text-xs text-text-muted">Internal note (optional)</label>
                <textarea id="replace_note" name="note" rows="2" maxlength="500" class="w-full rounded-lg border border-border-default bg-elevated px-3 py-2 text-sm" placeholder="Why this domain was changed…">{{ old('note') }}</textarea>
            </div>
            <x-dashboard.button type="submit" variant="primary">Save new domain</x-dashboard.button>
        </form>
    </x-dashboard.card>
</x-layout.page>
@endsection
