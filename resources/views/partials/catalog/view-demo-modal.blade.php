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
    $showTutorial = filled($product->tutorial_url);
@endphp

@if ($showDemo || $showTutorial)
    <div class="inline-flex flex-wrap items-center gap-2">
        @if ($showDemo)
            @auth
                @include('partials.catalog.product-demo-actions', [
                    'product' => $product,
                    'useDashboardModal' => false,
                ])
            @else
                <x-ui.button :href="route('login')" variant="secondary" size="lg" class="!bg-slate-100 !text-slate-800 !border-slate-200">
                    Log in to view demo
                </x-ui.button>
                @if ($showTutorial)
                    <x-ui.button
                        :href="$product->tutorial_url"
                        variant="secondary"
                        size="lg"
                        class="!bg-slate-100 !text-slate-800 !border-slate-200 hover:!bg-slate-200"
                        target="_blank"
                        rel="noopener"
                    >
                        Watch tutorial
                    </x-ui.button>
                @endif
            @endauth
        @elseif ($showTutorial)
            <x-ui.button
                :href="$product->tutorial_url"
                variant="secondary"
                size="lg"
                class="!bg-slate-100 !text-slate-800 !border-slate-200 hover:!bg-slate-200"
                target="_blank"
                rel="noopener"
            >
                Watch tutorial
            </x-ui.button>
        @endif
    </div>
@endif
