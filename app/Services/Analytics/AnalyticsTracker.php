<?php

namespace App\Services\Analytics;

use App\Contracts\Analytics\MarketingAnalyticsProviderInterface;
use App\Services\Analytics\Providers\GoogleAnalyticsProvider;
use Illuminate\Support\Facades\Request;

class AnalyticsTracker
{
    /** @var list<MarketingAnalyticsProviderInterface> */
    private array $marketingProviders;

    public function __construct(
        private UserActivityRecorder $activity,
        GoogleAnalyticsProvider $googleAnalytics,
    ) {
        $this->marketingProviders = [$googleAnalytics];
    }

    public function pageView(?int $userId = null, ?string $path = null, ?string $title = null): void
    {
        $path = $path ?: Request::path();
        $title = $title ?: config('app.name');

        foreach ($this->marketingProviders as $provider) {
            if ($provider->isEnabled()) {
                $provider->trackPageView($path, $title, $userId);
            }
        }

        if ($userId) {
            $this->activity->record($userId, 'page_view', null, 'page.view', [
                'path' => $path,
                'title' => $title,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function event(string $name, array $params = [], ?int $userId = null): void
    {
        foreach ($this->marketingProviders as $provider) {
            if ($provider->isEnabled()) {
                $provider->trackEvent($name, $params, $userId);
            }
        }

        if ($userId) {
            $this->activity->record($userId, 'event', null, 'event.'.$name, $params);
        }
    }

    public function purchase(float $amount, string $currency, ?int $userId = null, array $params = []): void
    {
        $payload = array_merge($params, [
            'amount' => $amount,
            'currency' => $currency,
        ]);

        foreach ($this->marketingProviders as $provider) {
            if ($provider->isEnabled()) {
                $provider->trackEvent('purchase', $payload, $userId);
            }
        }

        if ($userId) {
            $this->activity->record($userId, 'purchase', null, 'commerce.purchase', $payload);
        }

        $this->activity->incrementDaily('commerce.purchase', $currency);
    }

    public function login(?int $userId = null, string $method = 'password'): void
    {
        foreach ($this->marketingProviders as $provider) {
            if ($provider->isEnabled()) {
                $provider->trackEvent('login', ['method' => $method], $userId);
            }
        }

        if ($userId) {
            $this->activity->record($userId, 'login', null, 'auth.login', ['method' => $method]);
        }

        $this->activity->incrementDaily('auth.login');
    }
}
