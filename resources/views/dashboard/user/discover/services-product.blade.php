@extends('layouts.dashboard-user')

@section('title', $product->title)

@section('content')
@php
    $crumbs = [
        ['Dashboard', route('dashboard')],
        ['Services', route('dashboard.services')],
    ];
    if ($groupSlug && $groupLabel) {
        $crumbs[] = [$groupLabel, route('dashboard.services.browse', $groupSlug)];
    }
    $crumbs[] = [$product->title, null];
    $variants = $product->activeVariants;
@endphp
<x-layout.page
    :title="$product->title"
    :subtitle="$product->short_description"
    width="default"
    :breadcrumb="$crumbs"
>
    <x-slot:actions>
        @if($wallet)
            <span class="text-sm text-text-muted mr-2">Wallet: <strong class="text-text-primary">₦{{ number_format((float) $wallet->balance, 0) }}</strong></span>
        @endif
        <x-dashboard.button :href="route('dashboard.services.checkout', $product->slug)" variant="primary" size="sm" icon="orders">Buy with wallet</x-dashboard.button>
        @php
            $demoIntegration = $product->siteIntegration;
            $canDemoUser = $demoIntegration?->isActive() && $demoIntegration->hasCapability(\App\Models\SiteIntegration::CAP_DEMO_USER_LOGIN) && filled($demoIntegration->demo_user_email);
            $canDemoAdmin = $demoIntegration?->isActive() && $demoIntegration->hasCapability(\App\Models\SiteIntegration::CAP_DEMO_ADMIN_LOGIN) && filled($demoIntegration->demo_admin_email);
        @endphp
        @if ($canDemoUser || $canDemoAdmin)
            <x-dashboard.button type="button" variant="secondary" size="sm" x-on:click="$dispatch('open-modal', 'view-demo-dash-{{ $product->id }}')">View Demo</x-dashboard.button>
            <x-dashboard.modal name="view-demo-dash-{{ $product->id }}" maxWidth="md">
                <div class="space-y-4 p-1">
                    <h3 class="text-lg font-semibold text-text-primary">View demo</h3>
                    <p class="text-sm text-text-secondary">Open the independent demo site without a password.</p>
                    <div class="flex flex-col gap-2">
                        @if ($canDemoUser)
                            <form method="POST" action="{{ route('dashboard.services.demo-launch', [$product, 'user']) }}">
                                @csrf
                                <x-dashboard.button type="submit" class="w-full" variant="secondary">Login as User</x-dashboard.button>
                            </form>
                        @endif
                        @if ($canDemoAdmin)
                            <form method="POST" action="{{ route('dashboard.services.demo-launch', [$product, 'admin']) }}">
                                @csrf
                                <x-dashboard.button type="submit" class="w-full">Login as Admin</x-dashboard.button>
                            </form>
                        @endif
                    </div>
                </div>
            </x-dashboard.modal>
        @endif
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <x-dashboard.card class="space-y-4">
            @if($product->hero_image || $product->heroMedia)
                <img
                    src="{{ $product->heroMedia?->url('medium') ?? asset($product->hero_image) }}"
                    alt="{{ $product->title }}"
                    class="w-full max-h-72 rounded-xl object-cover"
                >
            @endif
            @if(filled($product->description))
                <div class="prose prose-sm max-w-none text-text-secondary whitespace-pre-line">{{ $product->description }}</div>
            @elseif(filled($product->short_description))
                <p class="text-text-secondary">{{ $product->short_description }}</p>
            @endif
        </x-dashboard.card>

        <x-dashboard.card class="space-y-4 h-fit">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">From</p>
                <p class="text-3xl font-bold text-primary mt-1">₦{{ number_format($product->displayPrice(), 0) }}</p>
            </div>
            @if($variants->isNotEmpty())
                <div class="space-y-2">
                    <p class="text-sm font-medium text-text-secondary">Available plans</p>
                    @foreach($variants->take(4) as $variant)
                        <div class="flex items-center justify-between text-sm border border-border-subtle rounded-lg px-3 py-2">
                            <span>{{ $variant->displayLabel() }}</span>
                            <span class="font-semibold">₦{{ number_format((float) $variant->price, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            <x-dashboard.button :href="route('dashboard.services.checkout', $product->slug)" variant="primary" icon="orders" class="w-full">Continue to checkout</x-dashboard.button>
            @if($groupSlug)
                <a href="{{ route('dashboard.services.browse', $groupSlug) }}" class="inline-flex text-sm text-text-secondary hover:text-primary">← Back to {{ $groupLabel ?? 'services' }}</a>
            @endif
        </x-dashboard.card>
    </div>
</x-layout.page>
@endsection
