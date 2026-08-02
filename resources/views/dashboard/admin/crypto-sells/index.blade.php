@extends('layouts.dashboard-admin')

@section('title', 'Crypto Sells')

@section('content')
@php
    $filtered = request()->hasAny(['q', 'user_id', 'status', 'coin', 'date_from', 'date_to']);
@endphp
<x-layout.page
    title="Crypto sell requests"
    subtitle="Search by tracking code, TX hash, user, status, or date."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Crypto sell requests', null],
    ]"
>
    <x-dashboard.table
        :empty="$requests->isEmpty()"
        :empty-title="$filtered ? 'No sells match these filters' : 'No crypto sell requests'"
        :empty-description="$filtered ? 'Clear filters to see all orders.' : 'User sell quotes awaiting on-chain confirmation will appear here.'"
        :empty-action="$filtered ? ['href' => route('admin.crypto-sells'), 'label' => 'Clear filters'] : null"
        empty-icon="bitcoin"
        striped
    >
        <x-slot:filters>
            <x-dashboard.filter-bar>
                <form method="GET" class="contents">
                    <div class="min-w-[14rem] flex-1">
                        <x-dashboard.input name="q" type="search" :value="$filters['q'] ?? ''" placeholder="OTC code, TX hash, email…" />
                    </div>
                    <div class="min-w-[10rem]">
                        <x-dashboard.select name="user_id">
                            <option value="">All users</option>
                            @foreach ($filterUsers as $u)
                                <option value="{{ $u->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $u->id)>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </x-dashboard.select>
                    </div>
                    <div class="min-w-[9rem]">
                        <x-dashboard.select name="status">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ str_replace('_', ' ', $st) }}</option>
                            @endforeach
                        </x-dashboard.select>
                    </div>
                    <div class="min-w-[7rem]">
                        <x-dashboard.select name="coin">
                            <option value="">All coins</option>
                            @foreach ($coins as $c)
                                <option value="{{ $c }}" @selected(($filters['coin'] ?? '') === strtoupper((string) $c))>{{ $c }}</option>
                            @endforeach
                        </x-dashboard.select>
                    </div>
                    <div class="min-w-[9rem]">
                        <x-dashboard.input name="date_from" type="date" :value="$filters['date_from'] ?? ''" label="" />
                    </div>
                    <div class="min-w-[9rem]">
                        <x-dashboard.input name="date_to" type="date" :value="$filters['date_to'] ?? ''" label="" />
                    </div>
                    <x-dashboard.button type="submit" variant="secondary" size="md">Filter</x-dashboard.button>
                    @if(request()->hasAny(['q','user_id','status','coin','date_from','date_to']))
                        <x-dashboard.button :href="route('admin.crypto-sells')" variant="ghost" size="md">Clear</x-dashboard.button>
                    @endif
                </form>
            </x-dashboard.filter-bar>
        </x-slot:filters>

        <x-slot:head>
            <x-dashboard.th>Tracking</x-dashboard.th>
            <x-dashboard.th>User</x-dashboard.th>
            <x-dashboard.th>Trade</x-dashboard.th>
            <x-dashboard.th>Match</x-dashboard.th>
            <x-dashboard.th>Expires</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($requests as $r)
            <tr>
                <x-dashboard.td>
                    <span class="font-mono text-xs text-text-primary">{{ $r->tracking_code ?: '—' }}</span>
                </x-dashboard.td>
                <x-dashboard.td>{{ \App\Models\User::labelFor($r->user) }}</x-dashboard.td>
                <x-dashboard.td>
                    ${{ number_format((float) ($r->amount_usd ?? 0), 2) }}
                    · {{ $r->amount_crypto }} {{ $r->coin }}
                    → ₦{{ number_format($r->expected_ngn, 2) }}
                </x-dashboard.td>
                <x-dashboard.td class="text-xs text-text-secondary">{{ $r->amount_match_status ?: '—' }}</x-dashboard.td>
                <x-dashboard.td class="text-text-secondary text-xs">{{ $r->expires_at }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$r->status" />
                </x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.button :href="route('admin.crypto-sells.show', $r)" size="sm" variant="secondary">Review</x-dashboard.button>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$requests" />
    </x-slot:pagination>
</x-layout.page>
@endsection
