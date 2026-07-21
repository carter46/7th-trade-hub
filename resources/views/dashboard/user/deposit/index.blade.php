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
        <x-dashboard.button :href="route('dashboard.deposit.create-bank')" icon="deposit">Deposit via Bank</x-dashboard.button>
        <x-dashboard.button :href="route('dashboard.crypto-sell.create')" variant="secondary" icon="bitcoin">Sell Crypto</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$fundings->isEmpty()"
        empty-title="No deposits yet"
        empty-description="Submit a bank deposit or sell crypto to fund your wallet."
        empty-icon="deposit"
        :empty-action="['href' => route('dashboard.deposit.create-bank'), 'label' => 'Deposit via Bank']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Reference</x-dashboard.th>
            <x-dashboard.th>Method</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
        </x-slot:head>
        @foreach ($fundings as $f)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-medium">{{ $f->reference }}</x-dashboard.td>
                <x-dashboard.td>{{ $f->method }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($f->amount, 2) }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$f->status" /></x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$fundings" />
    </x-slot:pagination>
</x-layout.page>
@endsection
