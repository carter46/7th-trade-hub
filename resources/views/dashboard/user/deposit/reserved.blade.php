@extends('layouts.dashboard-user')

@section('title', 'Reserved account')

@section('content')
<x-layout.page
    title="Reserved deposit account"
    subtitle="Transfer any amount to this account. Your wallet is credited after Monnify confirms payment."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Deposit', route('dashboard.deposit.index')],
        ['Reserved account', null],
    ]"
>
    <x-dashboard.card>
        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-text-muted">Bank</dt>
                <dd class="text-lg font-semibold text-text-primary">{{ $account['bankName'] }}</dd>
            </div>
            <div>
                <dt class="text-text-muted">Account number</dt>
                <dd class="text-lg font-semibold text-text-primary">{{ $account['accountNumber'] }}</dd>
            </div>
            <div>
                <dt class="text-text-muted">Reference</dt>
                <dd>{{ $account['accountReference'] }}</dd>
            </div>
        </dl>
    </x-dashboard.card>
</x-layout.page>
@endsection
