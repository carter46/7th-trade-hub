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
                <dd class="font-medium text-text-primary">{{ $connection->verified_at?->format('j M Y H:i') ?? '—' }}</dd>
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
            Connection domains use the existing DNS / verify workflow. Manual registration reject/replace does not apply.
        </x-dashboard.alert>

        @if($connection->verification_status !== 'verified')
            <form method="POST" action="{{ route('admin.users.domains.connections.approve', [$user, $connection]) }}">
                @csrf
                <x-dashboard.button type="submit" variant="secondary">Approve domain connection</x-dashboard.button>
            </form>
        @endif
    </x-dashboard.card>
</x-layout.page>
@endsection
