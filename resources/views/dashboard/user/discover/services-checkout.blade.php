@extends('layouts.dashboard-user')

@section('title', 'Checkout — '.$product->title)

@section('content')
@php
    $hasWallet = (bool) ($wallet ?? null);
    $gatewayOn = (bool) ($gatewayEnabled ?? false);
    $defaultMethod = $hasWallet ? 'wallet' : ($gatewayOn ? 'gateway' : 'wallet');
    $variantPayload = $variants->map(fn ($v) => [
        'id' => $v->id,
        'price' => (float) $v->price,
        'label' => $v->displayLabel(),
        'description' => (string) ($v->description ?? ''),
        'is_default' => (bool) $v->is_default,
    ])->values();
@endphp
<x-layout.page
    title="Checkout"
    :subtitle="$product->title"
    width="default"
    :breadcrumb="[
        ['Dashboard', route('dashboard')],
        ['Services', route('dashboard.services')],
        [$product->title, route('dashboard.services.product', $product->slug)],
        ['Checkout', null],
    ]"
>
    <x-dashboard.card class="max-w-lg space-y-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-primary mb-1">Platform service</p>
            <h2 class="text-xl font-semibold text-text-primary">{{ $product->title }}</h2>
            @if(filled($product->short_description))
                <p class="text-sm text-text-secondary mt-1">{{ $product->short_description }}</p>
            @endif
        </div>

        @if(session('error'))
            <x-dashboard.alert type="danger">{{ session('error') }}</x-dashboard.alert>
        @endif

        @if(! auth()->user()->hasVerifiedEmail())
            <x-dashboard.alert type="warning">
                <a href="{{ route('verification.notice') }}" class="underline font-medium">Verify your email</a> before purchasing.
            </x-dashboard.alert>
        @elseif(! $hasWallet && ! $gatewayOn)
            <x-dashboard.alert type="warning">
                No payment method is available. <a href="{{ route('dashboard.wallet') }}" class="underline font-medium">Create a wallet</a>
                or ask an admin to enable card/transfer checkout.
            </x-dashboard.alert>
        @else
            <form
                method="POST"
                action="{{ route('dashboard.services.purchase', $product->slug) }}"
                class="space-y-5"
                x-data="platformCheckout(@js($variantPayload), @js([
                    'defaultVariantId' => $defaultVariantId,
                    'basePrice' => $basePrice,
                    'paymentMethod' => $defaultMethod,
                ]))"
            >
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                @if ($renewTool ?? null)
                    <input type="hidden" name="renew_user_tool_id" value="{{ $renewTool->id }}">
                    <x-dashboard.alert type="info">
                        Renewing <strong>{{ $renewTool->resolvedDisplayName() }}</strong>. This extends the same tool — it will not create a second instance.
                    </x-dashboard.alert>
                @endif

                @if($variants->isNotEmpty())
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2">Plan / variant</label>
                        <div class="space-y-2">
                            @foreach($variants as $variant)
                                <label
                                    class="flex cursor-pointer flex-col gap-1 rounded-xl border border-border-default px-4 py-3 hover:border-primary/40"
                                    :class="Number(variantId) === {{ (int) $variant->id }} ? 'border-primary bg-primary/5' : ''"
                                >
                                    <span class="flex items-center justify-between gap-3">
                                        <span class="flex items-center gap-3">
                                            <input
                                                type="radio"
                                                name="variant_id"
                                                value="{{ $variant->id }}"
                                                @checked((int) $defaultVariantId === (int) $variant->id)
                                                x-model.number="variantId"
                                            >
                                            <span class="text-sm text-text-primary">{{ $variant->displayLabel() }}</span>
                                        </span>
                                        <span class="font-semibold text-text-primary">₦{{ number_format($variant->price, 2) }}</span>
                                    </span>
                                    @if(filled($variant->description))
                                        <span class="pl-7 text-xs leading-relaxed text-text-secondary">{{ $variant->description }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Payment method</label>
                    <div class="space-y-2">
                        @if($hasWallet)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-border-default px-4 py-3"
                                   :class="paymentMethod === 'wallet' ? 'border-primary bg-primary/5' : 'hover:border-primary/40'">
                                <input type="radio" name="payment_method" value="wallet" x-model="paymentMethod" class="mt-1 accent-primary" @checked($defaultMethod === 'wallet')>
                                <span>
                                    <span class="block text-sm font-medium text-text-primary">Wallet balance</span>
                                    <span class="block text-xs text-text-muted">Available: ₦{{ number_format((float) $wallet->balance, 2) }}</span>
                                </span>
                            </label>
                        @endif
                        @if($gatewayOn)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-border-default px-4 py-3"
                                   :class="paymentMethod === 'gateway' ? 'border-primary bg-primary/5' : 'hover:border-primary/40'">
                                <input type="radio" name="payment_method" value="gateway" x-model="paymentMethod" class="mt-1 accent-primary" @checked($defaultMethod === 'gateway')>
                                <span>
                                    <span class="block text-sm font-medium text-text-primary">Pay directly</span>
                                    <span class="block text-xs text-text-muted">Card or bank transfer via payment gateway</span>
                                </span>
                            </label>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Quantity</label>
                    <input type="number" name="quantity" min="1" max="100" x-model.number="qty" class="w-32 rounded-lg border-border-default bg-elevated text-text-primary text-sm">
                </div>

                @if($showDomainOptions)
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2">Domain (optional)</label>
                        <select name="domain_mode" x-model="domainMode" class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm mb-3">
                            <option value="none">No domain needed</option>
                            <option value="buy">Buy a domain</option>
                            <option value="connect">Connect existing domain</option>
                        </select>
                        <input type="text" name="domain_name" x-show="domainMode !== 'none'" placeholder="example.com" class="w-full rounded-lg border-border-default bg-elevated text-text-primary text-sm">
                    </div>
                @else
                    <input type="hidden" name="domain_mode" value="none">
                @endif

                <div class="flex items-center justify-between border-t border-border-default pt-4">
                    <span class="text-text-secondary">Total</span>
                    <span class="text-2xl font-bold text-primary" x-text="'₦' + totalFormatted"></span>
                </div>

                <x-dashboard.button type="submit" icon="orders" class="w-full">
                    <span x-show="paymentMethod === 'wallet'">Pay from wallet</span>
                    <span x-cloak x-show="paymentMethod === 'gateway'">Continue to payment</span>
                </x-dashboard.button>
            </form>
        @endif

        <a href="{{ route('dashboard.services.product', $product->slug) }}" class="inline-flex text-sm text-text-secondary hover:text-primary">
            ← Back to product
        </a>
    </x-dashboard.card>
</x-layout.page>
@endsection
