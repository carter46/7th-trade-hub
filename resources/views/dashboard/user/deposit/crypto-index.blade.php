@extends('layouts.dashboard-user')

@section('title', 'Sell Crypto')

@section('content')
<x-layout.page
    title="Sell Crypto (OTC)"
    subtitle="Quotes expire after 15 minutes — refresh if needed."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Sell Crypto', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('dashboard.crypto-sell.create')" icon="plus">New Sell Request</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$requests->isEmpty()"
        empty-title="No sell requests yet"
        empty-description="Create a quote to sell crypto and receive NGN in your wallet."
        empty-icon="bitcoin"
        :empty-action="['href' => route('dashboard.crypto-sell.create'), 'label' => 'New Sell Request']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Coin</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Expected NGN</x-dashboard.th>
            <x-dashboard.th>Expires</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($requests as $r)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-medium">{{ $r->coin }}</x-dashboard.td>
                <x-dashboard.td>{{ $r->amount_crypto }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($r->expected_ngn, 2) }}</x-dashboard.td>
                <x-dashboard.td class="text-text-secondary text-xs">{{ $r->expires_at->format('H:i') }}</x-dashboard.td>
                <x-dashboard.td>
                    @if ($r->status === 'pending' && $r->isQuoteExpired())
                        <x-dashboard.badge status="warning">Expired</x-dashboard.badge>
                    @else
                        <x-dashboard.badge :status="$r->status" />
                    @endif
                </x-dashboard.td>
                <x-dashboard.td>
                    @if ($r->status === 'pending' && $r->isQuoteExpired())
                        <form method="POST" action="{{ route('dashboard.crypto-sell.refresh', $r) }}" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <x-dashboard.button type="submit" size="xs" variant="link" x-bind:disabled="submitting">Request New Quote</x-dashboard.button>
                        </form>
                    @endif
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$requests" />
    </x-slot:pagination>
</x-layout.page>
@endsection
