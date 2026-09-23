<?php

namespace App\Services\Domains;

use App\Models\DomainProvider;
use App\Models\PlatformProduct;
use App\Support\Domains\DomainProductTldPolicy;
use Illuminate\Validation\ValidationException;

class DomainProviderConfigValidator
{
    public function __construct(
        private DomainManualTldPriceService $manualPrices,
    ) {}

    /**
     * @throws ValidationException
     */
    public function assertValidConfiguration(): void
    {
        $enabled = DomainProvider::query()->where('enabled', true)->get();

        if ($enabled->isEmpty()) {
            $this->assertManualCommerceReady();

            return;
        }

        $defaults = $enabled->where('is_default', true);
        if ($defaults->count() !== 1) {
            throw ValidationException::withMessages([
                'is_default' => 'Exactly one enabled provider must be marked as default.',
            ]);
        }

        $priorities = $enabled
            ->reject(fn (DomainProvider $p) => $p->is_default)
            ->pluck('fallback_priority')
            ->filter(fn ($p) => $p !== null);

        if ($priorities->count() !== $priorities->unique()->count()) {
            throw ValidationException::withMessages([
                'fallback_priority' => 'Fallback priorities must be unique among enabled non-default providers.',
            ]);
        }
    }

    /**
     * When the last provider is turned off, manual TLD prices must already be configured.
     *
     * @throws ValidationException
     */
    public function assertManualCommerceReady(): void
    {
        $slug = (string) config('domains.registration_product_slug', 'domain-registration');
        $product = PlatformProduct::query()->where('slug', $slug)->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'enabled' => 'Create and publish the Domain product with manual extension prices before disabling all providers.',
            ]);
        }

        $allowed = DomainProductTldPolicy::allowedTlds($product);
        if ($allowed === []) {
            throw ValidationException::withMessages([
                'enabled' => 'Set allowed extensions and manual NGN prices on the Domain product before disabling all providers.',
            ]);
        }

        $priced = $this->manualPrices->pricesForProduct($product, activeOnly: true);
        $missing = [];
        foreach ($allowed as $tld) {
            if (! $priced->has($tld)) {
                $missing[] = '.'.$tld;
            }
        }

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'enabled' => 'Before disabling all domain providers, set manual NGN prices for every allowed extension on the Domain product (missing: '.implode(', ', $missing).').',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public function validateSave(DomainProvider $provider, bool $enabled, bool $isDefault, ?int $fallbackPriority): void
    {
        if ($enabled && $isDefault && $fallbackPriority !== null) {
            throw ValidationException::withMessages([
                'fallback_priority' => 'Default provider cannot have a fallback priority.',
            ]);
        }

        if ($enabled && ! $isDefault && $fallbackPriority !== null) {
            $conflict = DomainProvider::query()
                ->where('enabled', true)
                ->where('is_default', false)
                ->where('id', '!=', $provider->id)
                ->where('fallback_priority', $fallbackPriority)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'fallback_priority' => 'Fallback priority must be unique among enabled non-default providers.',
                ]);
            }
        }

        if (! $enabled) {
            $othersEnabled = DomainProvider::query()
                ->where('enabled', true)
                ->where('id', '!=', $provider->id)
                ->exists();

            if (! $othersEnabled) {
                $this->assertManualCommerceReady();
            }
        }
    }
}
