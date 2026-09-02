@php
    $demoIntegration = $product->relationLoaded('siteIntegration')
        ? $product->siteIntegration
        : $product->siteIntegration()->where('status', \App\Enums\SiteIntegrationStatus::Active)->first();

    if ($demoIntegration && ! $demoIntegration->isActive()) {
        $demoIntegration = null;
    }

    $canDemoUser = $demoIntegration
        && $demoIntegration->hasCapability(\App\Models\SiteIntegration::CAP_DEMO_USER_LOGIN)
        && filled($demoIntegration->demo_user_email);
    $canDemoAdmin = $demoIntegration
        && $demoIntegration->hasCapability(\App\Models\SiteIntegration::CAP_DEMO_ADMIN_LOGIN)
        && filled($demoIntegration->demo_admin_email);
    $showDemo = $canDemoUser || $canDemoAdmin;
    $modalName = $modalName ?? 'view-demo-product-'.$product->id;
    $buttonVariant = $buttonVariant ?? 'secondary';
    $buttonSize = $buttonSize ?? 'sm';
    $useDashboardModal = $useDashboardModal ?? true;
@endphp

@if ($showDemo)
    @if ($useDashboardModal)
        <x-dashboard.button type="button" :variant="$buttonVariant" :size="$buttonSize" x-on:click="$dispatch('open-modal', '{{ $modalName }}')">
            View Demo
        </x-dashboard.button>
        <x-dashboard.modal :name="$modalName" maxWidth="md">
            @include('partials.catalog._demo-launch-modal-body', [
                'product' => $product,
                'canDemoUser' => $canDemoUser,
                'canDemoAdmin' => $canDemoAdmin,
                'dashboard' => true,
                'modalName' => $modalName,
            ])
        </x-dashboard.modal>
    @else
        <div x-data="{ open: false }" class="inline-flex">
            <x-ui.button
                type="button"
                variant="secondary"
                size="lg"
                class="!bg-slate-100 !text-slate-800 !border-slate-200 hover:!bg-slate-200"
                x-on:click="open = true"
            >
                View Demo
            </x-ui.button>
            <div
                x-show="open"
                x-cloak
                class="fixed inset-0 z-[80] flex items-center justify-center p-4"
                @keydown.escape.window="open = false"
            >
                <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
                <div class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                    @include('partials.catalog._demo-launch-modal-body', [
                        'product' => $product,
                        'canDemoUser' => $canDemoUser,
                        'canDemoAdmin' => $canDemoAdmin,
                        'dashboard' => false,
                        'closeAction' => 'open = false',
                    ])
                </div>
            </div>
        </div>
    @endif
@endif
