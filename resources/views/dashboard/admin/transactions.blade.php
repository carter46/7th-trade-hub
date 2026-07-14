@extends('layouts.dashboard-admin')

@section('title', 'Transactions')

@section('content')
<x-layout.page title="Transactions" subtitle="View and manage all transactions." width="full">
    <x-ui.table
        :empty="$transactions->isEmpty()"
        empty-title="No transactions yet"
        empty-description="Platform transactions will appear here."
        empty-icon="transactions"
        striped
    >
        <x-slot:head>
            <x-ui.th>Reference</x-ui.th>
            <x-ui.th>User</x-ui.th>
            <x-ui.th>Type</x-ui.th>
            <x-ui.th>Label</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Date</x-ui.th>
        </x-slot:head>

        @foreach ($transactions as $tx)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-mono text-xs">{{ $tx->reference }}</x-ui.td>
                <x-ui.td>{{ $tx->user?->name ?? $tx->user?->email ?? '—' }}</x-ui.td>
                <x-ui.td>{{ $tx->type }}</x-ui.td>
                <x-ui.td>{{ $tx->label }}</x-ui.td>
                <x-ui.td>{{ $tx->currency }} {{ number_format($tx->amount, 2) }}</x-ui.td>
                <x-ui.td><x-ui.badge :status="$tx->status" /></x-ui.td>
                <x-ui.td class="text-text-muted text-xs">{{ $tx->created_at->format('M j, Y H:i') }}</x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$transactions" />
    </x-slot:pagination>
</x-layout.page>
@endsection
