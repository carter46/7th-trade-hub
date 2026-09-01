@extends('layouts.dashboard-user')

@section('title', 'Bank transfer — '.$order->reference)

@section('content')
@php
    $meta = $order->payment_metadata ?? [];
    $proofSubmitted = filled($order->payment_submitted_at);
@endphp
<x-layout.page
    title="Complete bank transfer"
    width="default"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Service orders', route('dashboard.service-orders')],
        [$order->reference, null],
    ]"
>
    <x-dashboard.card class="w-full space-y-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-primary mb-1">Pending payment</p>
            <h2 class="text-xl font-semibold text-text-primary">Order {{ $order->reference }}</h2>
            <p class="text-2xl font-bold text-primary mt-2">₦{{ number_format((float) $order->total_amount, 2) }}</p>
        </div>

        @if(session('status'))
            <x-dashboard.alert type="success">{{ session('status') }}</x-dashboard.alert>
        @endif
        @if(session('error'))
            <x-dashboard.alert type="danger">{{ session('error') }}</x-dashboard.alert>
        @endif

        @if(filled($bankDetails['bank_name'] ?? null) && filled($bankDetails['account_number'] ?? null))
            <div class="rounded-xl border border-border-default bg-muted/20 px-4 py-4 space-y-2 text-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Transfer to</p>
            <dl class="space-y-1">
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Bank</dt><dd class="font-medium text-text-primary">{{ $bankDetails['bank_name'] ?: '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Account number</dt><dd class="font-mono font-medium text-text-primary">{{ $bankDetails['account_number'] ?: '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-text-muted">Account name</dt><dd class="font-medium text-text-primary">{{ $bankDetails['account_name'] ?: '—' }}</dd></div>
            </dl>
            @if(filled($bankDetails['instructions'] ?? null))
                <p class="pt-2 text-text-secondary whitespace-pre-line">{{ $bankDetails['instructions'] }}</p>
            @endif
            <p class="pt-2 text-xs text-text-muted">Use order reference <strong>{{ $order->reference }}</strong> as your transfer narration where possible.</p>
        </div>
        @else
            <x-dashboard.alert type="warning">
                Bank transfer details are not configured yet. Please contact support with your order reference <strong>{{ $order->reference }}</strong>.
            </x-dashboard.alert>
        @endif

        @if($proofSubmitted)
            <x-dashboard.alert type="info">
                Payment details submitted on {{ $order->payment_submitted_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}.
                We will confirm your transfer shortly.
            </x-dashboard.alert>
        @endif

        <form method="POST" action="{{ route('dashboard.orders.manual-payment.submit', $order) }}" enctype="multipart/form-data" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-dashboard.input label="Your bank name" name="payer_bank_name" :value="old('payer_bank_name', $meta['payer_bank_name'] ?? '')" required />
            <x-dashboard.input label="Transfer reference" name="transfer_reference" :value="old('transfer_reference', $meta['transfer_reference'] ?? '')" required />
            <div class="space-y-1.5">
                <label for="proof" class="block text-sm font-medium text-text-secondary">Proof (optional)</label>
                <input
                    id="proof"
                    type="file"
                    name="proof"
                    accept=".jpg,.jpeg,.png,.pdf"
                    class="block w-full text-sm text-text-secondary file:mr-3 file:rounded-lg file:border-0 file:bg-elevated file:px-3 file:py-2 file:text-sm file:text-text-primary"
                />
            </div>
            <x-dashboard.button type="submit" icon="upload" x-bind:disabled="submitting">
                {{ $proofSubmitted ? 'Update payment details' : 'Submit payment details' }}
            </x-dashboard.button>
        </form>

        <a href="{{ route('dashboard.service-orders') }}" class="inline-flex text-sm text-text-secondary hover:text-primary">
            ← Back to service orders
        </a>
    </x-dashboard.card>
</x-layout.page>
@endsection
