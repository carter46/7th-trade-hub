@extends('layouts.dashboard-user')

@section('title', 'Withdrawal')

@section('content')
<x-layout.page
    title="Withdrawal {{ $withdrawal->reference }}"
    subtitle="Status: {{ $withdrawal->status }}{{ $withdrawal->internal_status ? ' / '.$withdrawal->internal_status : '' }}"
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Withdraw', route('dashboard.withdrawal.index')],
        [$withdrawal->reference, null],
    ]"
>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-dashboard.card>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-text-muted">Amount</dt><dd class="font-medium">₦{{ number_format($withdrawal->amount, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-text-muted">Bank</dt><dd>{{ $withdrawal->bank_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-text-muted">Account</dt><dd>{{ $withdrawal->maskedAccountNumber() }}</dd></div>
                <div class="flex justify-between"><dt class="text-text-muted">Name</dt><dd>{{ $withdrawal->account_name }}</dd></div>
            </dl>
            @if ($withdrawal->isOpen())
                <p class="mt-4 text-sm text-text-secondary">This withdrawal is in progress. You cannot cancel or change the bank until it finishes.</p>
            @endif
        </x-dashboard.card>
        <x-dashboard.card>
            <h3 class="font-semibold text-text-primary mb-3">Timeline</h3>
            <ol class="space-y-3 border-l border-border-subtle pl-4">
                @forelse ($withdrawal->timelineEvents as $event)
                    <li>
                        <p class="text-xs text-text-muted">{{ $event->occurred_at?->format('H:i') }} · {{ $event->occurred_at?->toDayDateTimeString() }}</p>
                        <p class="text-sm font-medium text-text-primary">{{ $event->label }}</p>
                    </li>
                @empty
                    <li class="text-sm text-text-muted">No timeline events yet.</li>
                @endforelse
            </ol>
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
