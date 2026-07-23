<?php

namespace App\Providers;

use App\Contracts\Analytics\AnalyticsServiceInterface;
use App\Contracts\Analytics\HeatmapProviderInterface;
use App\Contracts\Analytics\MarketingAnalyticsProviderInterface;
use App\Events\CryptoSold;
use App\Events\EscrowDisputed;
use App\Events\EscrowOpened;
use App\Events\EscrowReleased;
use App\Events\ListingApproved;
use App\Events\ListingRejected;
use App\Events\OrderCompleted;
use App\Events\TicketOpened;
use App\Events\TicketReplied;
use App\Events\UserRegistered;
use App\Events\UserVerified;
use App\Events\WalletFunded;
use App\Listeners\DispatchMarketingAnalytics;
use App\Listeners\NotifyAdmins;
use App\Listeners\NotifyUsersFromEvent;
use App\Listeners\RecordProductActivity;
use App\Listeners\WriteAuditLogFromEvent;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Marketplace\Services\CheckoutService;
use App\Modules\Wallet\Contracts\WalletProviderInterface;
use App\Modules\Wallet\Providers\ManualProvider;
use App\Modules\Wallet\Services\CryptoPriceService;
use App\Modules\Wallet\Services\WalletProvisioningService;
use App\Modules\Wallet\Services\WalletService;
use App\Services\Analytics\AnalyticsService;
use App\Services\Analytics\AnalyticsTracker;
use App\Services\Analytics\InternalBusinessProvider;
use App\Services\Analytics\ProductAnalyticsProvider;
use App\Services\Analytics\Providers\GoogleAnalyticsProvider;
use App\Services\Analytics\Providers\MicrosoftClarityProvider;
use App\Services\Analytics\UserActivityRecorder;
use App\Services\ThemeManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** @var list<class-string> */
    private array $analyticsEvents = [
        UserRegistered::class,
        UserVerified::class,
        WalletFunded::class,
        OrderCompleted::class,
        EscrowOpened::class,
        EscrowReleased::class,
        EscrowDisputed::class,
        ListingApproved::class,
        ListingRejected::class,
        TicketOpened::class,
        TicketReplied::class,
        CryptoSold::class,
    ];

    public function register(): void
    {
        $this->app->bind(WalletProviderInterface::class, ManualProvider::class);
        $this->app->singleton(WalletService::class);
        $this->app->singleton(CryptoPriceService::class);
        $this->app->singleton(WalletProvisioningService::class);
        $this->app->singleton(CheckoutService::class);
        $this->app->singleton(AuditLogService::class);
        $this->app->singleton(\App\Modules\Marketplace\Services\NotificationService::class);
        $this->app->singleton(\App\Services\Notifications\NotificationDispatcher::class);
        $this->app->singleton(ThemeManager::class);
        $this->app->singleton(\App\Services\Media\MediaPathService::class);

        $this->app->singleton(UserActivityRecorder::class);
        $this->app->singleton(GoogleAnalyticsProvider::class);
        $this->app->singleton(MicrosoftClarityProvider::class);
        $this->app->singleton(AnalyticsTracker::class);
        $this->app->singleton(InternalBusinessProvider::class);
        $this->app->singleton(ProductAnalyticsProvider::class);
        $this->app->singleton(AnalyticsService::class);

        $this->app->bind(MarketingAnalyticsProviderInterface::class, GoogleAnalyticsProvider::class);
        $this->app->bind(HeatmapProviderInterface::class, MicrosoftClarityProvider::class);
        $this->app->bind(AnalyticsServiceInterface::class, AnalyticsService::class);
    }

    public function boot(): void
    {
        $this->registerAnalyticsListeners();

        View::composer(['layouts.dashboard-user', 'layouts.dashboard-admin'], function ($view) {
            /** @var ThemeManager $themes */
            $themes = app(ThemeManager::class);
            $user = auth()->user();
            $preference = $themes->preferenceFor($user);
            $resolved = $themes->resolve($preference, ThemeManager::PREFERENCE_LIGHT);
            $payload = $themes->payloadFor($user, ThemeManager::PREFERENCE_LIGHT);

            $impersonatorName = null;
            if (session('impersonating') && session('impersonator_id')) {
                $impersonatorName = \App\Models\User::query()
                    ->whereKey(session('impersonator_id'))
                    ->value('name');
            }

            $view->with([
                'dashboardThemePreference' => $preference,
                'dashboardThemeResolved' => $resolved,
                'dashboardThemePayload' => $payload,
                'impersonatorName' => $impersonatorName,
            ]);
        });

        View::composer([
            'layouts.dashboard-user',
            'layouts.dashboard-admin',
            'layouts.marketing',
            'partials.analytics.tracker-scripts',
        ], function ($view) {
            $ga = app(GoogleAnalyticsProvider::class);
            $clarity = app(MicrosoftClarityProvider::class);

            $view->with([
                'analyticsMarketingScript' => $ga->isEnabled() ? $ga->measurementScript() : null,
                'analyticsHeatmapScript' => $clarity->isEnabled() ? $clarity->script() : null,
            ]);
        });
    }

    private function registerAnalyticsListeners(): void
    {
        $listeners = [
            [RecordProductActivity::class, 'handle'],
            [DispatchMarketingAnalytics::class, 'handle'],
            [WriteAuditLogFromEvent::class, 'handle'],
            [NotifyAdmins::class, 'handle'],
            [NotifyUsersFromEvent::class, 'handle'],
        ];

        foreach ($this->analyticsEvents as $event) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }
}
