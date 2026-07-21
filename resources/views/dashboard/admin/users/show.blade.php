@extends('layouts.dashboard-admin')

@section('title', $user->name)

@section('content')
@php
    $tabs = [
        ['id' => 'overview', 'label' => 'Overview', 'href' => route('admin.users.show', $user)],
        ['id' => 'wallet', 'label' => 'Wallet', 'href' => route('admin.users.wallet', $user)],
        ['id' => 'transactions', 'label' => 'Transactions', 'href' => route('admin.users.transactions', $user)],
        ['id' => 'orders', 'label' => 'Orders', 'href' => route('admin.users.orders', $user)],
        ['id' => 'listings', 'label' => 'Listings', 'href' => route('admin.users.listings', $user)],
        ['id' => 'escrows', 'label' => 'Escrows', 'href' => route('admin.users.escrows', $user)],
        ['id' => 'tickets', 'label' => 'Support', 'href' => route('admin.users.tickets', $user)],
        ['id' => 'activity', 'label' => 'Activity', 'href' => route('admin.users.activity', $user)],
        ['id' => 'security', 'label' => 'Security', 'href' => route('admin.users.security', $user)],
    ];
@endphp
<x-layout.page
    title="{{ $user->name }}"
    subtitle="{{ $user->email }}"
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Users', route('admin.users')],
        [$user->name, null],
    ]"
>
    <x-slot:actions>
        @if (! $user->anonymized_at)
            <x-dashboard.button :href="route('admin.users.edit', $user)" variant="secondary" size="sm">Edit</x-dashboard.button>
            @if ($user->is_suspended)
                <form method="POST" action="{{ route('admin.users.restore', $user) }}">
                    @csrf
                    <x-dashboard.button type="submit" variant="success" size="sm">Restore</x-dashboard.button>
                </form>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Permanently delete this user? This anonymizes personal data and cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <x-dashboard.button type="submit" variant="danger" size="sm">Permanently Delete</x-dashboard.button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                    @csrf
                    <x-dashboard.button type="submit" variant="danger" size="sm">Suspend</x-dashboard.button>
                </form>
            @endif
        @endif
    </x-slot:actions>

    @if (session('status'))
        <x-dashboard.alert variant="success">{{ session('status') }}</x-dashboard.alert>
    @endif
    @if (session('error'))
        <x-dashboard.alert variant="error">{{ session('error') }}</x-dashboard.alert>
    @endif

    <div class="flex flex-wrap items-center gap-3 text-sm text-text-secondary">
        <x-dashboard.badge :status="$user->anonymized_at ? 'neutral' : ($user->is_suspended ? 'suspended' : 'active')" />
        <span>Joined {{ $user->created_at->format('j M Y') }}</span>
        @if ($user->username)
            <span>@{{ $user->username }}</span>
        @endif
    </div>

    <x-dashboard.tabs :tabs="$tabs" :active="$activeTab" class="mt-2" />

    <div class="mt-6 space-y-4">
        @if ($activeTab === 'overview')
            <div class="grid gap-4 md:grid-cols-3">
                <x-dashboard.stats-card label="Orders" :value="number_format($orderCount ?? 0)" icon="orders" />
                <x-dashboard.stats-card label="Listings" :value="number_format($listingCount ?? 0)" icon="listings" />
                <x-dashboard.stats-card label="Tickets" :value="number_format($ticketCount ?? 0)" icon="support" />
            </div>
            <x-dashboard.card>
                <h3 class="mb-3 text-sm font-semibold text-text-primary">Wallet</h3>
                <p class="text-text-secondary">
                    @if ($wallet ?? null)
                        Balance: ₦{{ number_format($wallet->balance ?? 0, 2) }}
                    @else
                        No wallet provisioned.
                    @endif
                </p>
            </x-dashboard.card>
            <x-dashboard.card>
                <h3 class="mb-3 text-sm font-semibold text-text-primary">Recent transactions</h3>
                @forelse (($recentTransactions ?? collect()) as $tx)
                    <div class="flex items-center justify-between border-b border-border-default py-2 text-sm last:border-0">
                        <span class="font-mono text-xs">{{ $tx->reference ?? $tx->id }}</span>
                        <span>{{ number_format($tx->amount, 2) }} {{ $tx->currency }}</span>
                        <x-dashboard.badge :status="$tx->status" />
                    </div>
                @empty
                    <p class="text-sm text-text-muted">No transactions.</p>
                @endforelse
            </x-dashboard.card>
        @elseif ($activeTab === 'wallet')
            <x-dashboard.card>
                @if ($wallet ?? null)
                    <dl class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-text-muted">Balance</dt>
                            <dd class="text-lg font-semibold text-text-primary">₦{{ number_format($wallet->balance ?? 0, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-text-muted">Status</dt>
                            <dd class="text-text-primary">{{ $wallet->status ?? 'active' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-text-muted">No wallet.</p>
                @endif
            </x-dashboard.card>
        @elseif ($activeTab === 'transactions')
            <x-dashboard.table :empty="($transactions ?? collect())->isEmpty()" empty-title="No transactions" striped>
                <x-slot:head>
                    <x-dashboard.th>Reference</x-dashboard.th>
                    <x-dashboard.th>Amount</x-dashboard.th>
                    <x-dashboard.th>Status</x-dashboard.th>
                    <x-dashboard.th>Date</x-dashboard.th>
                </x-slot:head>
                @foreach ($transactions as $tx)
                    <tr>
                        <x-dashboard.td class="font-mono text-xs">{{ $tx->reference ?? $tx->id }}</x-dashboard.td>
                        <x-dashboard.td>{{ number_format($tx->amount, 2) }} {{ $tx->currency }}</x-dashboard.td>
                        <x-dashboard.td><x-dashboard.badge :status="$tx->status" /></x-dashboard.td>
                        <x-dashboard.td class="text-xs text-text-muted">{{ $tx->created_at->format('j M Y') }}</x-dashboard.td>
                    </tr>
                @endforeach
            </x-dashboard.table>
            <x-dashboard.pagination :paginator="$transactions" />
        @elseif ($activeTab === 'orders')
            <x-dashboard.table :empty="($orders ?? collect())->isEmpty()" empty-title="No orders" striped>
                <x-slot:head>
                    <x-dashboard.th>ID</x-dashboard.th>
                    <x-dashboard.th>Status</x-dashboard.th>
                    <x-dashboard.th>Date</x-dashboard.th>
                </x-slot:head>
                @foreach ($orders as $order)
                    <tr>
                        <x-dashboard.td>#{{ $order->id }}</x-dashboard.td>
                        <x-dashboard.td><x-dashboard.badge :status="$order->status" /></x-dashboard.td>
                        <x-dashboard.td class="text-xs text-text-muted">{{ $order->created_at->format('j M Y') }}</x-dashboard.td>
                    </tr>
                @endforeach
            </x-dashboard.table>
            <x-dashboard.pagination :paginator="$orders" />
        @elseif ($activeTab === 'listings')
            <x-dashboard.table :empty="($listings ?? collect())->isEmpty()" empty-title="No listings" striped>
                <x-slot:head>
                    <x-dashboard.th>Title</x-dashboard.th>
                    <x-dashboard.th>Status</x-dashboard.th>
                    <x-dashboard.th>Date</x-dashboard.th>
                </x-slot:head>
                @foreach ($listings as $listing)
                    <tr>
                        <x-dashboard.td>{{ $listing->title ?? ('Listing #'.$listing->id) }}</x-dashboard.td>
                        <x-dashboard.td><x-dashboard.badge :status="$listing->status ?? ($listing->is_active ? 'active' : 'inactive')" /></x-dashboard.td>
                        <x-dashboard.td class="text-xs text-text-muted">{{ $listing->created_at->format('j M Y') }}</x-dashboard.td>
                    </tr>
                @endforeach
            </x-dashboard.table>
            <x-dashboard.pagination :paginator="$listings" />
        @elseif ($activeTab === 'escrows')
            <x-dashboard.table :empty="($escrows ?? collect())->isEmpty()" empty-title="No escrows" striped>
                <x-slot:head>
                    <x-dashboard.th>ID</x-dashboard.th>
                    <x-dashboard.th>Status</x-dashboard.th>
                    <x-dashboard.th>Date</x-dashboard.th>
                </x-slot:head>
                @foreach ($escrows as $escrow)
                    <tr>
                        <x-dashboard.td>#{{ $escrow->id }}</x-dashboard.td>
                        <x-dashboard.td><x-dashboard.badge :status="$escrow->status" /></x-dashboard.td>
                        <x-dashboard.td class="text-xs text-text-muted">{{ $escrow->created_at->format('j M Y') }}</x-dashboard.td>
                    </tr>
                @endforeach
            </x-dashboard.table>
            <x-dashboard.pagination :paginator="$escrows" />
        @elseif ($activeTab === 'tickets')
            <x-dashboard.table :empty="($tickets ?? collect())->isEmpty()" empty-title="No tickets" striped>
                <x-slot:head>
                    <x-dashboard.th>Subject</x-dashboard.th>
                    <x-dashboard.th>Status</x-dashboard.th>
                    <x-dashboard.th>Date</x-dashboard.th>
                </x-slot:head>
                @foreach ($tickets as $ticket)
                    <tr>
                        <x-dashboard.td>{{ $ticket->subject ?? ('Ticket #'.$ticket->id) }}</x-dashboard.td>
                        <x-dashboard.td><x-dashboard.badge :status="$ticket->status" /></x-dashboard.td>
                        <x-dashboard.td class="text-xs text-text-muted">{{ $ticket->created_at->format('j M Y') }}</x-dashboard.td>
                    </tr>
                @endforeach
            </x-dashboard.table>
            <x-dashboard.pagination :paginator="$tickets" />
        @elseif ($activeTab === 'activity')
            <x-dashboard.table :empty="($activity ?? collect())->isEmpty()" empty-title="No activity" striped>
                <x-slot:head>
                    <x-dashboard.th>Action</x-dashboard.th>
                    <x-dashboard.th>Date</x-dashboard.th>
                </x-slot:head>
                @foreach ($activity as $row)
                    <tr>
                        <x-dashboard.td>{{ $row->action ?? $row->event ?? '—' }}</x-dashboard.td>
                        <x-dashboard.td class="text-xs text-text-muted">{{ $row->created_at->format('j M Y H:i') }}</x-dashboard.td>
                    </tr>
                @endforeach
            </x-dashboard.table>
            <x-dashboard.pagination :paginator="$activity" />
        @elseif ($activeTab === 'security')
            <x-dashboard.card class="space-y-2 text-sm text-text-secondary">
                <p>Suspended: {{ $user->is_suspended ? 'Yes' : 'No' }}</p>
                <p>Anonymized: {{ $user->anonymized_at ? $user->anonymized_at->format('j M Y H:i') : 'No' }}</p>
                <p>Email verified: {{ $user->email_verified_at ? 'Yes' : 'No' }}</p>
                <p>KYC level: {{ $user->kyc_level }}</p>
            </x-dashboard.card>
        @endif
    </div>
</x-layout.page>
@endsection
