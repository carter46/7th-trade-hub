@extends('layouts.dashboard-admin')

@section('title', 'Crypto Sells')

@section('content')
<x-layout.page
    title="Crypto sell requests"
    subtitle="Verify on-chain deposits and credit locked NGN quotes."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Crypto sell requests', null],
    ]"
>
    <x-dashboard.table :empty="$requests->isEmpty()" empty-title="No crypto sell requests" empty-description="User sell quotes awaiting on-chain confirmation will appear here." empty-icon="bitcoin" striped>
        <x-slot:head>
            <x-dashboard.th>User</x-dashboard.th>
            <x-dashboard.th>Trade</x-dashboard.th>
            <x-dashboard.th>Match</x-dashboard.th>
            <x-dashboard.th>Expires</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($requests as $r)
            <tr>
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
