@extends('layouts.dashboard-user')

@section('title', 'Replace bank')

@section('content')
@php $step = session('bank_replace_step', old('_step', 'password')); @endphp
<x-layout.page
    title="Replace Bank Account"
    subtitle="Password → email OTP → Monnify name enquiry → confirm."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Banks', route('dashboard.banks.index')],
        ['Replace', null],
    ]"
>
    <x-dashboard.card>
        @if ($step === 'password')
            <form method="POST" action="{{ route('dashboard.banks.replace.otp') }}" class="space-y-4">
                @csrf
                <x-dashboard.input type="password" name="password" label="Confirm your password" required autocomplete="current-password" />
                <x-dashboard.button type="submit">Send email code</x-dashboard.button>
            </form>
        @elseif ($step === 'otp')
            <form method="POST" action="{{ route('dashboard.banks.replace.verify-otp') }}" class="space-y-4">
                @csrf
                <x-dashboard.input name="otp" label="6-digit verification code" maxlength="6" required />
                <x-dashboard.button type="submit">Verify code</x-dashboard.button>
            </form>
        @elseif ($step === 'bank')
            <form method="POST" action="{{ route('dashboard.banks.replace.resolve') }}" class="space-y-4" x-data="{ bankName: '' }">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Bank</label>
                    <select name="bank_code" class="w-full rounded-lg border border-border-subtle px-3 py-2 text-sm" required @change="bankName = $event.target.selectedOptions[0]?.dataset.name || ''">
                        <option value="">Select bank</option>
                        @foreach ($banks as $b)
                            <option value="{{ $b['code'] }}" data-name="{{ $b['name'] }}">{{ $b['name'] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="bank_name" :value="bankName">
                </div>
                <x-dashboard.input name="account_number" label="Account number" required />
                <x-dashboard.button type="submit">Resolve account name</x-dashboard.button>
            </form>
        @elseif ($step === 'confirm' && $resolved)
            <div class="mb-4 rounded-xl border border-border-subtle p-4 text-sm space-y-1">
                <p><strong>Bank:</strong> {{ $resolved['bankName'] }}</p>
                <p><strong>Account:</strong> {{ $resolved['accountNumber'] }}</p>
                <p><strong>Account name:</strong> {{ $resolved['accountName'] }}</p>
            </div>
            <form method="POST" action="{{ route('dashboard.banks.replace.confirm') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="bank_code" value="{{ $resolved['bankCode'] }}">
                <input type="hidden" name="bank_name" value="{{ $resolved['bankName'] }}">
                <input type="hidden" name="account_number" value="{{ $resolved['accountNumber'] }}">
                <input type="hidden" name="verified_name" value="{{ $resolved['accountName'] }}">
                <x-dashboard.button type="submit" variant="primary">Confirm &amp; save</x-dashboard.button>
            </form>
        @else
            <p class="text-sm text-text-secondary">Start again from the beginning.</p>
            <x-dashboard.button :href="route('dashboard.banks.replace')" class="mt-4">Restart</x-dashboard.button>
        @endif
    </x-dashboard.card>
</x-layout.page>
@endsection
