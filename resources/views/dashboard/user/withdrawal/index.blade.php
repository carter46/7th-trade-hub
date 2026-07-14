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
        <x-ui.button :href="route('dashboard.withdrawal.create')" icon="withdraw">New withdrawal</x-ui.button>
    </x-slot:actions>

    <x-ui.table
        :empty="$withdrawals->isEmpty()"
        empty-title="No withdrawals yet"
        empty-description="Request a bank withdrawal when you have available NGN balance."
        empty-icon="withdraw"
        :empty-action="['href' => route('dashboard.withdrawal.create'), 'label' => 'New withdrawal']"
        striped
    >
        <x-slot:head>
            <x-ui.th>Reference</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Status</x-ui.th>
        </x-slot:head>
        @foreach ($withdrawals as $w)
            <tr class="hover:bg-muted/50">
                <x-ui.td class="font-medium">{{ $w->reference }}</x-ui.td>
                <x-ui.td>₦{{ number_format($w->amount, 2) }}</x-ui.td>
                <x-ui.td><x-ui.badge :status="$w->status" /></x-ui.td>
            </tr>
        @endforeach
    </x-ui.table>

    <x-slot:pagination>
        <x-ui.pagination :paginator="$withdrawals" />
    </x-slot:pagination>
</x-layout.page>
@endsection
