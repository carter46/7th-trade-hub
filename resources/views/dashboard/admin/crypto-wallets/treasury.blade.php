@extends('layouts.dashboard-admin')

@section('title', 'Treasury Inventory')

@section('content')
@php
    $fmtUsd = fn ($n) => '≈ $'.number_format((float) $n, 2);
    $fmtNgn = fn ($n) => '≈ ₦'.number_format((float) $n, 0);
    $fmtCrypto = function ($amount, $coin) {
        $precision = (int) (config('crypto.amount_precision.'.strtoupper($coin)) ?? 8);
        return rtrim(rtrim(number_format((float) $amount, $precision, '.', ''), '0'), '.') ?: '0';
    };
@endphp
<x-layout.page
    title="Treasury Inventory"
    subtitle="Live on-chain balances (current balance, not total received). Reserved = open OTC orders."
    width="full"
    :breadcrumb="[['Admin', route('admin')], ['Deposit wallets', route('admin.crypto-wallets')], ['Treasury', null]]"
>
    <x-slot:actions>
        <form method="POST" action="{{ route('admin.crypto-wallets.treasury.refresh') }}">
            @csrf
            <x-dashboard.button type="submit" size="sm">Refresh balances now</x-dashboard.button>
        </form>
    </x-slot:actions>

    <x-dashboard.alert variant="info" class="mb-4">
        Explorers distinguish <strong>total received</strong> (lifetime) from <strong>current balance</strong>. Treasury uses current balance only. Exchange-managed wallets may show near-zero after auto-sweep — that is expected.
    </x-dashboard.alert>

    <x-dashboard.stat-grid>
        <x-dashboard.stats-card
            label="Total portfolio (USD)"
            :value="$fmtUsd($portfolioUsd)"
            icon="wallet"
        />
        <x-dashboard.stats-card
            label="Total portfolio (NGN)"
            :value="$fmtNgn($portfolioNgn)"
            icon="wallet"
        />
    </x-dashboard.stat-grid>

    @if (! empty($coinCards))
        <div class="mt-4 flex flex-wrap gap-2 text-xs text-text-muted">
            @foreach ($coinCards as $card)
                <span class="rounded-lg border border-border-default bg-elevated px-2.5 py-1">
                    {{ $card['coin'] }} {{ $card['allocation_pct'] }}%
                </span>
            @endforeach
        </div>
    @endif

    <div class="mt-6 space-y-4" x-data="{ open: @js(array_key_first($coinCards) ?: '') }">
        @forelse ($coinCards as $coin => $card)
            <div class="rounded-2xl border border-border-default bg-elevated overflow-hidden">
                <button
                    type="button"
                    class="flex w-full items-start justify-between gap-4 px-4 py-4 text-left hover:bg-surface/40"
                    @click="open = open === @js($coin) ? '' : @js($coin)"
                >
                    <div>
                        <p class="text-sm font-semibold text-text-primary">{{ $coin }}</p>
                        <p class="mt-0.5 text-xs text-text-muted">
                            {{ $card['wallet_count'] }} wallet{{ $card['wallet_count'] === 1 ? '' : 's' }}
                            · {{ $fmtCrypto($card['total_balance'], $coin) }} {{ $coin }}
                            · {{ $fmtUsd($card['usd']) }}
                            · {{ $fmtNgn($card['ngn']) }}
                            · {{ $card['allocation_pct'] }}% of portfolio
                        </p>
                    </div>
                    <span class="text-xs text-text-muted" x-text="open === @js($coin) ? 'Hide' : 'Show'"></span>
                </button>

                <div x-show="open === @js($coin)" x-cloak class="border-t border-border-default">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-surface/50 text-left text-xs text-text-muted">
                                <tr>
                                    <th class="px-4 py-2 font-medium">Network</th>
                                    <th class="px-4 py-2 font-medium">Address</th>
                                    <th class="px-4 py-2 font-medium">Status</th>
                                    <th class="px-4 py-2 font-medium">Open</th>
                                    <th class="px-4 py-2 font-medium">Current</th>
                                    <th class="px-4 py-2 font-medium">Reserved</th>
                                    <th class="px-4 py-2 font-medium">Available</th>
                                    <th class="px-4 py-2 font-medium">Synced</th>
                                    <th class="px-4 py-2 font-medium">History</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($card['rows'] as $row)
                                    @php
                                        $w = $row['wallet'];
                                        $networkLabel = app(\App\Modules\Wallet\Services\NetworkRegistry::class)->label((string) $w->network);
                                    @endphp
                                    <tr class="border-t border-border-default/60 align-top">
                                        <td class="px-4 py-3 text-xs">{{ $networkLabel }}</td>
                                        <td class="px-4 py-3 font-mono text-xs break-all max-w-[12rem]">{{ $w->address }}</td>
                                        <td class="px-4 py-3 text-xs">
                                            <div>{{ $w->is_active ? 'Active' : 'Disabled' }}</div>
                                            @if ($w->is_exchange_managed)
                                                <span class="mt-1 inline-block rounded-md bg-warning/15 px-1.5 py-0.5 text-[10px] font-medium text-warning">
                                                    Exchange-managed — balance may reset after sweep
                                                </span>
                                            @endif
                                            @if ($w->live_balance_error)
                                                <p class="mt-1 text-[11px] text-danger">{{ $w->live_balance_error }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">{{ $row['open_orders'] }}</td>
                                        <td class="px-4 py-3">{{ $w->live_balance !== null ? $fmtCrypto($row['current'], $coin) : '—' }}</td>
                                        <td class="px-4 py-3">{{ $fmtCrypto($row['reserved'], $coin) }}</td>
                                        <td class="px-4 py-3">{{ $fmtCrypto($row['available'], $coin) }}</td>
                                        <td class="px-4 py-3 text-xs text-text-muted">
                                            {{ $w->live_balance_updated_at ? $w->live_balance_updated_at->diffForHumans() : 'Never' }}
                                        </td>
                                        <td class="px-4 py-3 text-[11px] text-text-muted">
                                            @forelse ($row['history'] as $h)
                                                <div>{{ $fmtCrypto($h->balance, $coin) }} · {{ $h->recorded_at?->diffForHumans() }}</div>
                                            @empty
                                                —
                                            @endforelse
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <x-dashboard.empty title="No wallets" icon="wallet" description="Add deposit wallets to start tracking treasury balances." />
        @endforelse
    </div>
</x-layout.page>
@endsection
