@extends('layouts.dashboard-user')

@section('title', 'History')

@section('content')
<x-layout.page
    title="Transaction History"
    subtitle="Your full wallet ledger."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['History', null],
    ]"
>
    <x-ui.table
        :empty="$transactions->isEmpty()"
        empty-title="No transactions yet"
        empty-description="Wallet credits, debits, and marketplace payments will appear here."
        empty-icon="history"
        striped
    >
        <x-slot:head>
            <x-ui.th>Ref</x-ui.th>
            <x-ui.th>Type</x-ui.th>
            <x-ui.th>Label</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
        </x-slot:head>
        @foreach ($transactions as $tx)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-medium">{{ $tx->reference }}</x-ui.td>
                <x-ui.td>{{ $tx->type }}</x-ui.td>
                <x-ui.td>{{ $tx->label }}</x-ui.td>
                <x-ui.td>₦{{ number_format($tx->amount, 2) }}</x-ui.td>
                <x-ui.td><x-ui.badge :status="$tx->status" /></x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$transactions" />
    </x-slot:pagination>
</x-layout.page>
@endsection
