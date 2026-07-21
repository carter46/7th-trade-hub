@extends('layouts.dashboard-user')

@section('title', 'Withdrawals')

@section('content')
<x-layout.page
    title="Withdrawal History"
    subtitle="Bank payout requests and their status."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Withdraw', null],
    ]"
>
    <x-slot:actions>
        <x-dashboard.button :href="route('dashboard.withdrawal.create')" icon="withdraw">New withdrawal</x-dashboard.button>
    </x-slot:actions>

    <x-dashboard.table
        :empty="$withdrawals->isEmpty()"
        empty-title="No withdrawals yet"
        empty-description="Request a bank withdrawal when you have available NGN balance."
        empty-icon="withdraw"
        :empty-action="['href' => route('dashboard.withdrawal.create'), 'label' => 'New withdrawal']"
        striped
    >
        <x-slot:head>
            <x-dashboard.th>Reference</x-dashboard.th>
            <x-dashboard.th>Amount</x-dashboard.th>
            <x-dashboard.th>Status</x-dashboard.th>
        </x-slot:head>
        @foreach ($withdrawals as $w)
            <tr class="hover:bg-muted/50">
                <x-dashboard.td class="font-medium">{{ $w->reference }}</x-dashboard.td>
                <x-dashboard.td>₦{{ number_format($w->amount, 2) }}</x-dashboard.td>
                <x-dashboard.td><x-dashboard.badge :status="$w->status" /></x-dashboard.td>
            </tr>
        @endforeach
    </x-dashboard.table>

    <x-slot:pagination>
        <x-dashboard.pagination :paginator="$withdrawals" />
    </x-slot:pagination>
</x-layout.page>
@endsection
