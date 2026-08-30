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
@endphp

@if ($showDemo)
    <div x-data="{ open: false }" class="contents">
        @auth
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
                <div class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-4 text-slate-900">
                    <h3 class="text-lg font-semibold">View demo</h3>
                    <p class="text-sm text-slate-600">Open the independent demo site as a fixed demo account. No password required.</p>
                    <div class="flex flex-col gap-2">
                        @if ($canDemoUser)
                            <form method="POST" action="{{ route('dashboard.services.demo-launch', [$product, 'user']) }}">
                                @csrf
                                <x-ui.button type="submit" variant="secondary" class="w-full">Login as User</x-ui.button>
                            </form>
                        @endif
                        @if ($canDemoAdmin)
                            <form method="POST" action="{{ route('dashboard.services.demo-launch', [$product, 'admin']) }}">
                                @csrf
                                <x-ui.button type="submit" variant="primary" class="w-full">Login as Admin</x-ui.button>
                            </form>
                        @endif
                    </div>
                    <button type="button" class="w-full text-sm text-slate-500 hover:text-slate-800" @click="open = false">Cancel</button>
                </div>
            </div>
        @else
            <x-ui.button :href="route('login')" variant="secondary" size="lg" class="!bg-slate-100 !text-slate-800 !border-slate-200">
                Log in to view demo
            </x-ui.button>
        @endauth
    </div>
@endif
