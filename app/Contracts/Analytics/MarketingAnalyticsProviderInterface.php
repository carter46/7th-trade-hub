<?php

namespace App\Contracts\Analytics;

interface MarketingAnalyticsProviderInterface
{
    public function isEnabled(): bool;

    public function measurementScript(): ?string;

    public function trackPageView(string $path, ?string $title = null, ?int $userId = null): void;

    /**
     * @param  array<string, mixed>  $params
     */
    public function trackEvent(string $name, array $params = [], ?int $userId = null): void;

    public function syncSnapshots(): int;

    /**
     * @return array{ok: bool, message: string, details?: array<string, mixed>}
     */
    public function connectionTest(): array;
}
