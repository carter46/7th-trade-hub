@extends('layouts.dashboard-admin')
@section('title', 'Exchange Rates')
@section('content')
<x-layout.page title="Exchange Rates" width="content">
    <x-dashboard.card class="mb-6">
        <form method="POST" action="{{ route('admin.exchange-rates.store') }}" class="grid md:grid-cols-3 gap-3">
            @csrf
            <x-dashboard.input label="Asset" name="asset" placeholder="BTC" required />
            <x-dashboard.input label="Buy rate (NGN)" type="number" step="0.01" name="buy_rate_ngn" required />
            <x-dashboard.input label="Sell rate (NGN)" type="number" step="0.01" name="sell_rate_ngn" required />
            <x-dashboard.input label="Minimum amount" type="number" step="any" name="minimum_amount" />
            <x-dashboard.input label="Maximum amount" type="number" step="any" name="maximum_amount" />
            <x-dashboard.input label="Processing time" name="processing_time" placeholder="5–15 minutes" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1"> Featured</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <x-dashboard.button type="submit">Save rate</x-dashboard.button>
        </form>
    </x-dashboard.card>
    <x-dashboard.table>
        <x-slot:head>
            <x-dashboard.th>Asset</x-dashboard.th>
            <x-dashboard.th>Buy</x-dashboard.th>
            <x-dashboard.th>Sell</x-dashboard.th>
            <x-dashboard.th>Time</x-dashboard.th>
            <x-dashboard.th></x-dashboard.th>
        </x-slot:head>
        @foreach($rates as $rate)
            <tr>
                <x-dashboard.td>{{ $rate->asset }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($rate->buy_rate_ngn, 2) }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($rate->sell_rate_ngn, 2) }}</x-dashboard.td>
                <x-dashboard.td>{{ $rate->processing_time }}</x-dashboard.td>
                <x-dashboard.td>
                    <form method="POST" action="{{ route('admin.exchange-rates.destroy', $rate) }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-danger text-sm">Delete</button>
                    </form>
                </x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>
</x-layout.page>
@endsection
