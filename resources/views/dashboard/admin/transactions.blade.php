@extends('layouts.dashboard-admin')

@section('title', 'Transactions')

@section('content')
<x-layout.page
    title="Transactions"
    subtitle="View and manage all transactions."
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Transactions', null],
    ]"
>
    <x-dashboard.table
        :empty="$transactions->isEmpty()"
        empty-title="No transactions yet"
        empty-description="Platform transactions will appear here."
        empty-icon="transactions"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Reference</x-dashboard.th>
            <x-dashboard.th>User</x-dashboard.th>
            <x-dashboard.th>Type</x-dashboard.th>
            <x-dashboard.th>Label</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
            <x-dashboard.th>Date</x-dashboard.th>
        </x-slot:head>

        @foreach ($transactions as $tx)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-mono text-xs">{{ $tx->reference }}</x-dashboard.td>
                <x-dashboard.td>{{ $tx->user?->name ?? $tx->user?->email ?? '—' }}</x-dashboard.td>
                <x-dashboard.td>{{ $tx->type }}</x-dashboard.td>
                <x-dashboard.td>{{ $tx->label }}</x-dashboard.td>
                <x-dashboard.td>{{ $tx->currency }} {{ number_format($tx->amount, 2) }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$tx->status" /></x-dashboard.td>
                <x-dashboard.td class="text-text-muted text-xs">{{ $tx->created_at->format('M j, Y H:i') }}</x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$transactions" />
    </x-slot:pagination>
</x-layout.page>
@endsection
