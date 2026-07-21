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
    <x-dashboard.table
        :empty="$transactions->isEmpty()"
        empty-title="No transactions yet"
        empty-description="Wallet credits, debits, and marketplace payments will appear here."
        empty-icon="history"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Ref</x-dashboard.th>
            <x-dashboard.th>Type</x-dashboard.th>
            <x-dashboard.th>Label</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
        </x-slot:head>
        @foreach ($transactions as $tx)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-medium">{{ $tx->reference }}</x-dashboard.td>
                <x-dashboard.td>{{ $tx->type }}</x-dashboard.td>
                <x-dashboard.td>{{ $tx->label }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($tx->amount, 2) }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$tx->status" /></x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$transactions" />
    </x-slot:pagination>
</x-layout.page>
@endsection
