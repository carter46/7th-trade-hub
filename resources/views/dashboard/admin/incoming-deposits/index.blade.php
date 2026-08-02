@extends('layouts.dashboard-admin')

@section('title', 'Incoming Deposits')

@section('content')
<x-layout.page
    title="Incoming crypto deposits"
    subtitle="Detected by blockchain explorers. Matched deposits open the verification screen."
    width="full"
    :breadcrumb="[['Admin', route('admin')], ['Incoming deposits', null]]"
>
    <x-dashboard.table :empty="$deposits->isEmpty()" empty-title="No deposits detected" empty-icon="deposit" striped>
        <x-slot:head>
            <x-dashboard.th>When</x-dashboard.th>
            <x-dashboard.th>Asset</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>TX / Block</x-dashboard.th>
            <x-dashboard.th>Conf</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Order</x-dashboard.th>
            <x-dashboard.th></x-dashboard.th>
        </x-slot:head>
        @foreach ($deposits as $d)
            @php
                $url = \App\Modules\Wallet\Services\CryptoExplorerUrl::forTx($d->network, $d->tx_hash);
            @endphp
            <tr>
                <x-dashboard.td class="text-xs">{{ $d->detected_at }}</x-dashboard.td>
                <x-dashboard.td>{{ $d->coin }} / {{ $d->network }}</x-dashboard.td>
                <x-dashboard.td>{{ $d->amount }}</x-dashboard.td>
                <x-dashboard.td class="text-xs">
                    <span class="font-mono break-all">{{ \Illuminate\Support\Str::limit($d->tx_hash, 18) }}</span>
                    @if($url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-primary underline ml-1">Explorer</a>
                    @endif
                    <div class="text-text-muted">block {{ $d->block_height ?? '—' }}</div>
                </x-dashboard.td>
                <x-dashboard.td>{{ $d->confirmations }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$d->status" /></x-dashboard.td>
                <x-dashboard.td>
                    @if($d->matched_order_id)
                        <a href="{{ route('admin.crypto-sells.show', $d->matched_order_id) }}" class="text-primary underline">#{{ $d->matched_order_id }}</a>
                    @else
                        —
                    @endif
                </x-dashboard.td>
                <x-dashboard.td class="space-x-1">
                    @unless($d->matched_order_id || $d->status === 'ignored')
                        <form method="POST" action="{{ route('admin.incoming-deposits.rematch', $d) }}" class="inline">@csrf<button class="text-xs text-primary underline">Rematch</button></form>
                        <form method="POST" action="{{ route('admin.incoming-deposits.ignore', $d) }}" class="inline">@csrf<button class="text-xs text-danger underline">Ignore</button></form>
                    @endunless
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$deposits" />
    </x-slot:pagination>
</x-layout.page>
@endsection
