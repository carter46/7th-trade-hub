@extends('layouts.dashboard-user')

@section('title', 'Withdrawal bank')

@section('content')
<x-layout.page
    title="Withdrawal bank"
    subtitle="One active verified bank. Replace only — never edit in place."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Banks', null],
    ]"
>
    <x-dashboard.card>
        @if ($bank)
            <div class="space-y-2 text-sm">
                <p class="text-text-muted">Current bank</p>
                <p class="text-lg font-semibold text-text-primary">{{ $bank->bank_name }}</p>
                <p>{{ $bank->maskedAccountNumber() }}</p>
                <p class="text-text-secondary">{{ $bank->verified_name }}</p>
                <p class="text-xs text-text-muted">Verified {{ $bank->verified_at?->toDayDateTimeString() }} via {{ $bank->verified_by }}</p>
            </div>
            @if ($canReplace)
                <div class="mt-6">
                    <x-dashboard.button :href="route('dashboard.banks.replace')" variant="secondary">Replace Bank Account</x-dashboard.button>
                </div>
            @else
                <x-dashboard.alert type="warning" class="mt-6">
                    You cannot replace your withdrawal bank while a withdrawal request is pending or being processed.
                </x-dashboard.alert>
            @endif
        @else
            <p class="text-sm text-text-secondary mb-4">No withdrawal bank on file.</p>
            @if (! $monnifyReady)
                <x-dashboard.alert type="warning" class="mb-4">Bank verification is not available yet. Try again later.</x-dashboard.alert>
            @endif
            <x-dashboard.button :href="route('dashboard.banks.replace')" icon="withdraw">Add Bank Account</x-dashboard.button>
        @endif
    </x-dashboard.card>
</x-layout.page>
@endsection
