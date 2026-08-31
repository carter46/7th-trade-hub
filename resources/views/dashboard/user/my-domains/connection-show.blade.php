@extends('layouts.dashboard-user')

@section('title', 'Connect '.$connection->fqdn)

@section('content')
@php
    $detected = $connection->displayNameserverList();
    $required = $connection->requiredNameserverList();
    $atScan = $connection->nameserversAtScanList();
@endphp
<x-layout.page
    :title="$connection->fqdn"
    subtitle="Connected domain — verify nameservers to finish ownership."
    width="default"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['My Tools', route('dashboard.my-tools.domains')],
        [$connection->fqdn, null],
    ]"
>
    @if(session('status'))
        <x-dashboard.alert type="success">{{ session('status') }}</x-dashboard.alert>
    @endif
    @if(session('error'))
        <x-dashboard.alert type="danger">{{ session('error') }}</x-dashboard.alert>
    @endif

    <x-dashboard.card class="space-y-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Status</p>
            <div class="mt-1 flex flex-wrap items-center gap-2">
                <x-dashboard.badge :status="$connection->verification_status" />
                @if($connection->isVerified() && $connection->verified_at)
                    <span class="text-sm text-text-secondary">Verified {{ $connection->verified_at->format('j M Y') }}</span>
                @elseif($connection->isPending())
                    <span class="text-sm text-amber-700">Nameserver verification pending</span>
                @endif
            </div>
        </div>

        @if($connection->isPending())
            <x-dashboard.alert type="warning">
                Point this domain to our platform nameservers at your current registrar, then click <strong>Check status</strong>.
                DNS changes can take time to appear.
            </x-dashboard.alert>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-border-default bg-muted/20 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Detected nameservers</p>
                <ul class="mt-2 space-y-1 text-sm text-text-primary">
                    @forelse($detected as $host)
                        <li>{{ $host }}</li>
                    @empty
                        <li class="text-text-muted">Not checked yet</li>
                    @endforelse
                </ul>
                @if($atScan !== [] && $atScan !== $detected)
                    <p class="mt-3 text-xs text-text-muted">At checkout we saw:</p>
                    <ul class="mt-1 space-y-1 text-xs text-text-secondary">
                        @foreach($atScan as $host)
                            <li>{{ $host }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="rounded-xl border border-primary/30 bg-primary/5 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Required nameservers</p>
                <ul class="mt-2 space-y-1 text-sm text-text-primary">
                    @forelse($required as $host)
                        <li>{{ $host }}</li>
                    @empty
                        <li class="text-text-muted">Not configured</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <form method="POST" action="{{ route('dashboard.my-domains.connections.check', $connection) }}">
            @csrf
            <x-dashboard.button type="submit" variant="secondary">
                Check status
            </x-dashboard.button>
        </form>

        <a href="{{ route('dashboard.my-tools.domains') }}" class="inline-flex text-sm text-text-secondary hover:text-primary">
            ← Back to My Domains
        </a>
    </x-dashboard.card>
</x-layout.page>
@endsection
