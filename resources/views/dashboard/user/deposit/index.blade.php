@extends('layouts.dashboard-user')

@section('title', 'Deposit History')

@section('content')
<x-layout.page
    title="Deposit History"
    subtitle="Bank transfers and funding requests."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Deposit', null],
    ]"
>
    <x-slot:actions>
        <x-ui.button :href="route('dashboard.deposit.create-bank')" icon="deposit">Deposit via Bank</x-ui.button>
        <x-ui.button :href="route('dashboard.crypto-sell.create')" variant="secondary" icon="bitcoin">Sell Crypto</x-ui.button>
    </x-slot:actions>

    <x-ui.table
        :empty="$fundings->isEmpty()"
        empty-title="No deposits yet"
        empty-description="Submit a bank deposit or sell crypto to fund your wallet."
        empty-icon="deposit"
        :empty-action="['href' => route('dashboard.deposit.create-bank'), 'label' => 'Deposit via Bank']"
        striped
    >
        <x-slot:head>
            <x-ui.th>Reference</x-ui.th>
            <x-ui.th>Method</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
        </x-slot:head>
        @foreach ($fundings as $f)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-medium">{{ $f->reference }}</x-ui.td>
                <x-ui.td>{{ $f->method }}</x-ui.td>
                <x-ui.td>₦{{ number_format($f->amount, 2) }}</x-ui.td>
                <x-ui.td><x-ui.badge :status="$f->status" /></x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$fundings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
