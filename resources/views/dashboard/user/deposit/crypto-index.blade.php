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
        <x-ui.button :href="route('dashboard.crypto-sell.create')" icon="plus">New Sell Request</x-ui.button>
    </x-slot:actions>

    <x-ui.table
        :empty="$requests->isEmpty()"
        empty-title="No sell requests yet"
        empty-description="Create a quote to sell crypto and receive NGN in your wallet."
        empty-icon="bitcoin"
        :empty-action="['href' => route('dashboard.crypto-sell.create'), 'label' => 'New Sell Request']"
        striped
    >
        <x-slot:head>
            <x-ui.th>Coin</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Expected NGN</x-ui.th>
            <x-ui.th>Expires</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Actions</x-ui.th>
        </x-slot:head>
        @foreach ($requests as $r)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-medium">{{ $r->coin }}</x-ui.td>
                <x-ui.td>{{ $r->amount_crypto }}</x-ui.td>
                <x-ui.td>₦{{ number_format($r->expected_ngn, 2) }}</x-ui.td>
                <x-ui.td class="text-text-secondary text-xs">{{ $r->expires_at->format('H:i') }}</x-ui.td>
                <x-ui.td>
                    @if ($r->status === 'pending' && $r->isQuoteExpired())
                        <x-ui.badge status="warning">Expired</x-ui.badge>
                    @else
                        <x-ui.badge :status="$r->status" />
                    @endif
                </x-ui.td>
                <x-ui.td>
                    @if ($r->status === 'pending' && $r->isQuoteExpired())
                        <form method="POST" action="{{ route('dashboard.crypto-sell.refresh', $r) }}" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <x-ui.button type="submit" size="xs" variant="link" x-bind:disabled="submitting">Request New Quote</x-ui.button>
                        </form>
                    @endif
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$requests" />
    </x-slot:pagination>
</x-layout.page>
@endsection
