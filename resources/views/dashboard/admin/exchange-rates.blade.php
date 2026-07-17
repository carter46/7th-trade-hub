@extends('layouts.dashboard-admin')
@section('title', 'Exchange Rates')
@section('content')
<x-layout.page title="Exchange Rates" width="content">
    <x-ui.card class="mb-6">
        <form method="POST" action="{{ route('admin.exchange-rates.store') }}" class="grid md:grid-cols-3 gap-3">
            @csrf
            <x-ui.input label="Asset" name="asset" placeholder="BTC" required />
            <x-ui.input label="Buy rate (NGN)" type="number" step="0.01" name="buy_rate_ngn" required />
            <x-ui.input label="Sell rate (NGN)" type="number" step="0.01" name="sell_rate_ngn" required />
            <x-ui.input label="Minimum amount" type="number" step="any" name="minimum_amount" />
            <x-ui.input label="Maximum amount" type="number" step="any" name="maximum_amount" />
            <x-ui.input label="Processing time" name="processing_time" placeholder="5–15 minutes" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1"> Featured</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <x-ui.button type="submit">Save rate</x-ui.button>
        </form>
    </x-ui.card>
    <x-ui.card>
        <x-ui.table>
            <thead>
                <tr>
                    <x-ui.th>Asset</x-ui.th>
                    <x-ui.th>Buy</x-ui.th>
                    <x-ui.th>Sell</x-ui.th>
                    <x-ui.th>Time</x-ui.th>
                    <x-ui.th></x-ui.th>
                </tr>
            </thead>
            <tbody>
                @foreach($rates as $rate)
                    <tr>
                        <x-ui.td>{{ $rate->asset }}</x-ui.td>
                        <x-ui.td>₦{{ number_format($rate->buy_rate_ngn, 2) }}</x-ui.td>
                        <x-ui.td>₦{{ number_format($rate->sell_rate_ngn, 2) }}</x-ui.td>
                        <x-ui.td>{{ $rate->processing_time }}</x-ui.td>
                        <x-ui.td>
                            <form method="POST" action="{{ route('admin.exchange-rates.destroy', $rate) }}">
                                @csrf
                                @method('DELETE')
                                <button class="text-danger text-sm">Delete</button>
                            </form>
                        </x-ui.td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    </x-ui.card>
</x-layout.page>
@endsection
