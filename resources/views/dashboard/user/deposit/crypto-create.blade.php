@extends('layouts.dashboard-user')

@section('title', 'Sell Crypto')

@section('content')
<x-layout.page
    title="Sell Crypto"
    subtitle="Quotes use the platform sell rates set by admin. Valid for 15 minutes."
    width="full"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Sell Crypto', route('dashboard.crypto-sell.index')],
        ['New quote', null],
    ]"
>
    @if(empty($coins))
        <x-dashboard.card>
            <p class="text-sm text-text-muted">No active exchange rates are available right now. Please check back later.</p>
        </x-dashboard.card>
    @else
        <x-dashboard.card>
            <form
                method="POST"
                action="{{ route('dashboard.crypto-sell.store') }}"
                class="w-full space-y-4"
                x-data="cryptoSellForm(@js($rateMap))"
                @submit="submitting = true"
            >
                @csrf

                <div class="space-y-2">
                    <label for="coin" class="block text-sm font-medium text-text-primary">Coin</label>
                    <div class="flex items-center gap-3 rounded-xl border border-border-default bg-elevated px-3 py-2.5">
                        <template x-if="row?.logo">
                            <img :src="row.logo" alt="" class="h-8 w-8 rounded-full bg-white shrink-0" width="32" height="32" referrerpolicy="no-referrer">
                        </template>
                        <select
                            id="coin"
                            name="coin"
                            x-model="asset"
                            required
                            class="min-w-0 flex-1 border-0 bg-transparent text-sm font-semibold text-text-primary focus:outline-none focus:ring-0"
                        >
                            @foreach ($coins as $c)
                                <option value="{{ $c }}" @selected(old('coin') === $c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-text-muted" x-show="row">
                        Platform sell rate:
                        <span class="font-medium text-text-primary" x-text="row ? ('₦' + Number(row.sell).toLocaleString('en-NG') + ' / ' + asset) : ''"></span>
                    </p>
                </div>

                <x-dashboard.input label="Network" name="network" placeholder="e.g. TRC20" :value="old('network')" />
                <div>
                    <x-dashboard.input label="Amount" type="number" step="any" name="amount_crypto" required :value="old('amount_crypto')" x-model="amount" />
                    <p class="mt-1 text-xs text-text-muted" x-show="row?.min || row?.max">
                        <span x-show="row?.min" x-text="'Min ' + row.min"></span>
                        <span x-show="row?.min && row?.max"> · </span>
                        <span x-show="row?.max" x-text="'Max ' + row.max"></span>
                    </p>
                </div>

                <div class="rounded-xl border border-border-default bg-muted/40 px-4 py-3">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-text-muted">Estimated payout</p>
                    <p class="mt-1 font-display text-xl font-semibold text-text-primary">
                        ₦<span x-text="estimateFormatted">0</span>
                    </p>
                </div>

                <x-dashboard.button type="submit" icon="bitcoin" x-bind:disabled="submitting">Get Quote</x-dashboard.button>
            </form>
        </x-dashboard.card>
    @endif
</x-layout.page>
@endsection
