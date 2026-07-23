<?php

namespace App\Services\Analytics\Providers;

use App\Contracts\Analytics\MarketingAnalyticsProviderInterface;
use App\Models\AnalyticsGaSnapshot;
use App\Models\AnalyticsProvider;
use Illuminate\Support\Facades\Log;

class GoogleAnalyticsProvider implements MarketingAnalyticsProviderInterface
{
    public function isEnabled(): bool
    {
        return (bool) $this->config()?->enabled
            && filled($this->measurementId());
    }

    public function measurementScript(): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $measurementId = e($this->measurementId());

        return <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$measurementId}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$measurementId}');
</script>
HTML;
    }

    public function trackPageView(string $path, ?string $title = null, ?int $userId = null): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        Log::debug('analytics.ga.page_view', [
            'path' => $path,
            'title' => $title,
            'user_id' => $userId,
        ]);
    }

    public function trackEvent(string $name, array $params = [], ?int $userId = null): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        Log::debug('analytics.ga.event', [
            'name' => $name,
            'params' => $params,
            'user_id' => $userId,
        ]);
    }

    public function syncSnapshots(): int
    {
        if (! $this->isEnabled()) {
            return 0;
        }

        $periodStart = now()->subDays(7)->toDateString();
        $periodEnd = now()->toDateString();

        AnalyticsGaSnapshot::query()->create([
            'metric' => 'sessions',
            'dimension' => null,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'payload' => [
                'source' => 'stub',
                'measurement_id' => $this->measurementId(),
                'value' => 0,
            ],
            'fetched_at' => now(),
        ]);

        $config = $this->config();
        $config->forceFill([
            'last_sync_at' => now(),
            'status' => 'stub',
            'last_error' => 'Data API sync not configured; traffic KPIs hidden until connected.',
        ])->save();

        return 1;
    }

    public function connectionTest(): array
    {
        return $this->connectionTestFromInput([
            'measurement_id' => $this->measurementId(),
            'property_id' => $this->propertyId(),
        ]);
    }

    /**
     * @param  array{measurement_id?: string|null, property_id?: string|null}  $input
     * @return array{ok: bool, message: string, details?: array<string, mixed>}
     */
    public function connectionTestFromInput(array $input): array
    {
        $measurementId = $input['measurement_id'] ?? null;

        if (blank($measurementId)) {
            return ['ok' => false, 'message' => 'Measurement ID is required.'];
        }

        if (! preg_match('/^G-[A-Z0-9]+$/i', (string) $measurementId)) {
            return ['ok' => false, 'message' => 'Measurement ID must match the G-XXXXXXXXXX format.'];
        }

        return [
            'ok' => true,
            'message' => 'Measurement ID format is valid.',
            'details' => [
                'measurement_id' => $measurementId,
                'property_id' => $input['property_id'] ?? null,
            ],
        ];
    }

    private function config(): AnalyticsProvider
    {
        return AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_GOOGLE_ANALYTICS);
    }

    private function measurementId(): ?string
    {
        $id = $this->config()->credential('measurement_id');

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function propertyId(): ?string
    {
        $id = $this->config()->credential('property_id');

        return is_string($id) && $id !== '' ? $id : null;
    }
}
