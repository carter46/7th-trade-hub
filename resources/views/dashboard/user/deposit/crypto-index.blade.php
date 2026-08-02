@extends('layouts.dashboard-user')

@section('title', 'Sell Crypto')

@section('content')
<x-layout.page
    title="Sell Crypto (OTC)"
    subtitle="Quotes lock when created. Resume any active order from here."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Sell Crypto', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('dashboard.crypto-sell.create')" icon="plus">New Sell Request</x-dashboard.button>
    </x-slot:actions>

    @if($openOrder ?? null)
        <div class="mb-4 rounded-xl border border-primary/30 bg-primary/5 px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-text-primary">Active order {{ $openOrder->tracking_code }}</p>
                    <p class="text-xs text-text-muted">{{ $openOrder->coin }} · {{ str_replace('_', ' ', $openOrder->status) }}</p>
                </div>
                <x-dashboard.button :href="route('dashboard.crypto-sell.show', $openOrder)" size="sm">Resume order</x-dashboard.button>
            </div>
        </div>
    @endif

    <x-dashboard.table
        :empty="$requests->isEmpty()"
        empty-title="No sell requests yet"
        empty-description="Create a quote to sell crypto and receive NGN in your wallet."
        empty-icon="bitcoin"
        :empty-action="['href' => route('dashboard.crypto-sell.create'), 'label' => 'New Sell Request']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Tracking</x-dashboard.th>
            <x-dashboard.th>Coin</x-dashboard.th>
            <x-dashboard.th>USD / Crypto</x-dashboard.th>
            <x-dashboard.th>Expected NGN</x-dashboard.th>
            <x-dashboard.th>Expires</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Actions</x-dashboard.th>
        </x-slot:head>
        @foreach ($requests as $r)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-mono text-xs">{{ $r->tracking_code ?: '—' }}</x-dashboard.td>
                <x-dashboard.td class="font-medium">{{ $r->coin }}</x-dashboard.td>
                <x-dashboard.td class="text-sm">${{ number_format((float) ($r->amount_usd ?? 0), 2) }} · {{ $r->amount_crypto }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($r->expected_ngn, 2) }}</x-dashboard.td>
                <x-dashboard.td class="text-text-secondary text-xs">{{ $r->expires_at?->format('H:i') }}</x-dashboard.td>
                <x-dashboard.td>
                    <x-dashboard.badge :status="$r->status" />
                </x-dashboard.td>
                <x-dashboard.td class="space-x-2">
                    <x-dashboard.button :href="route('dashboard.crypto-sell.show', $r)" size="xs" variant="link">View</x-dashboard.button>
                    @if (in_array($r->status, ['expired', 'waiting_deposit'], true) && $r->isQuoteExpired())
                        <form method="POST" action="{{ route('dashboard.crypto-sell.refresh', $r) }}" class="inline" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <x-dashboard.button type="submit" size="xs" variant="link" x-bind:disabled="submitting">New Quote</x-dashboard.button>
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
