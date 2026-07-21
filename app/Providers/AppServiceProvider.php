<?php

namespace App\Providers;

use App\Modules\Wallet\Contracts\WalletProviderInterface;
use App\Modules\Wallet\Providers\ManualProvider;
use App\Modules\Wallet\Services\CryptoPriceService;
use App\Modules\Wallet\Services\WalletProvisioningService;
use App\Modules\Wallet\Services\WalletService;
use App\Modules\Marketplace\Services\CheckoutService;
use App\Modules\Admin\Services\AuditLogService;
use App\Services\ThemeManager;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WalletProviderInterface::class, ManualProvider::class);
        $this->app->singleton(WalletService::class);
        $this->app->singleton(CryptoPriceService::class);
        $this->app->singleton(WalletProvisioningService::class);
        $this->app->singleton(CheckoutService::class);
        $this->app->singleton(AuditLogService::class);
        $this->app->singleton(\App\Modules\Marketplace\Services\NotificationService::class);
        $this->app->singleton(ThemeManager::class);
    }

    public function boot(): void
    {
        View::composer(['layouts.dashboard-user', 'layouts.dashboard-admin'], function ($view) {
            /** @var ThemeManager $themes */
            $themes = app(ThemeManager::class);
            $user = auth()->user();
            $preference = $themes->preferenceFor($user);
            // Server-side resolve: system falls back to light until client detects OS preference.
            $resolved = $themes->resolve($preference, ThemeManager::PREFERENCE_LIGHT);
            $payload = $themes->payloadFor($user, ThemeManager::PREFERENCE_LIGHT);

            $view->with([
                'dashboardThemePreference' => $preference,
                'dashboardThemeResolved' => $resolved,
                'dashboardThemePayload' => $payload,
            ]);
        });
    }
}
