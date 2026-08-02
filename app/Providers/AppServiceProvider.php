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
use App\Events\WalletWithdrawalCompleted;
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
        WalletWithdrawalCompleted::class,
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
        $this->app->singleton(\App\Support\Demo\DemoBatchTracker::class);
        $this->app->bind(WalletProviderInterface::class, ManualProvider::class);
        $this->app->singleton(WalletService::class);
        $this->app->singleton(\App\Modules\Wallet\Payments\Monnify\MonnifyClient::class);
        $this->app->singleton(\App\Modules\Wallet\Payments\Monnify\MonnifyPaymentRail::class);
        $this->app->bind(
            \App\Modules\Wallet\Payments\Contracts\PaymentRailInterface::class,
            \App\Modules\Wallet\Payments\Monnify\MonnifyPaymentRail::class
        );
        $this->app->singleton(CryptoPriceService::class);
        $this->app->singleton(\App\Modules\Wallet\Services\ExchangeQuoteService::class);
        $this->app->singleton(WalletProvisioningService::class);
        $this->app->singleton(CheckoutService::class);
        $this->app->singleton(AuditLogService::class);
        $this->app->singleton(\App\Modules\Marketplace\Services\NotificationService::class);
        $this->app->singleton(\App\Services\Notifications\NotificationDispatcher::class);
        $this->app->singleton(ThemeManager::class);
        $this->app->singleton(\App\Services\Media\MediaPathService::class);
        $this->app->singleton(\App\Services\Branding\SiteBrandingRepository::class);
        $this->app->singleton(\App\Services\Communications\Contact\PlatformContactRepository::class);
        $this->app->singleton(\App\Services\Communications\Social\SocialLinkRepository::class);
        $this->app->singleton(\App\Services\Communications\LiveChat\LiveChatManager::class);
        $this->app->singleton(\App\Services\Communications\Email\EmailDeliveryLogger::class);
        $this->app->singleton(\App\Services\Communications\Email\EmailService::class);
        $this->app->singleton(\App\Services\Communications\Email\Providers\BrevoApiProvider::class);
        $this->app->singleton(\App\Services\Communications\Email\Providers\LaravelMailProvider::class);

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
        $this->applySiteBrandingConfig();

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

            $branding = app(\App\Services\Branding\SiteBrandingRepository::class)->all();
            $favicon = $branding['favicon_media_id']
                ? media_url_from_id($branding['favicon_media_id'], null, 'original')
                : null;

            $googleIdentityConfig = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('integration_providers')) {
                    $googleIdentityConfig = \App\Services\Auth\Identity\GoogleIdentityProvider::configForFrontend();
                }
            } catch (\Throwable) {
                $googleIdentityConfig = [];
            }

            $view->with([
                'dashboardThemePreference' => $preference,
                'dashboardThemeResolved' => $resolved,
                'dashboardThemePayload' => $payload,
                'impersonatorName' => $impersonatorName,
                'siteName' => $branding['site_name'],
                'siteBranding' => $branding,
                'faviconUrl' => $favicon,
                'googleIdentityConfig' => $googleIdentityConfig,
            ]);
        });

        View::composer(['layouts.marketing', 'layouts.auth', 'components.layouts.auth', 'auth.login', 'auth.register', 'pages.home'], function ($view) {
            $branding = app(\App\Services\Branding\SiteBrandingRepository::class)->all();
            $footer = \App\ViewModels\FooterViewModel::make();
            $favicon = $branding['favicon_media_id']
                ? media_url_from_id($branding['favicon_media_id'], null, 'original')
                : null;

            $googleIdentityConfig = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('integration_providers')) {
                    $googleIdentityConfig = \App\Services\Auth\Identity\GoogleIdentityProvider::configForFrontend();
                }
            } catch (\Throwable) {
                $googleIdentityConfig = [];
            }

            $view->with([
                'siteName' => $branding['site_name'],
                'siteHeading' => $branding['heading'],
                'siteTagline' => $branding['tagline'],
                'siteBranding' => $branding,
                'footer' => $footer,
                'faviconUrl' => $favicon,
                'googleIdentityConfig' => $googleIdentityConfig,
            ]);
        });
    }

    private function applySiteBrandingConfig(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
                return;
            }
            $branding = app(\App\Services\Branding\SiteBrandingRepository::class)->all();
            $name = $branding['site_name'] ?? '';
            if ($name !== '') {
                config(['app.name' => $name]);
                config([
                    'pwa.manifest.name' => $name,
                    'pwa.manifest.short_name' => ($branding['site_short_name'] ?? '') !== ''
                        ? $branding['site_short_name']
                        : $name,
                    'pwa.manifest.description' => ($branding['meta_description'] ?? '') !== ''
                        ? $branding['meta_description']
                        : config('pwa.manifest.description'),
                ]);
            }

            // Backfill PWA icons once when branding media exists but generated icons do not.
            $pwa = app(\App\Services\Branding\PwaBrandingSync::class);
            if (! $pwa->iconsExist()) {
                $pwa->sync($branding);
            }
        } catch (\Throwable) {
            // Database may be unavailable during early boot / package discovery.
        }
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
