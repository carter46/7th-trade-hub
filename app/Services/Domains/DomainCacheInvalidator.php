<?php

namespace App\Services\Domains;

use App\Models\PlatformProduct;
use Illuminate\Support\Facades\Cache;

class DomainCacheInvalidator
{
    public function invalidateTldAndPricing(): void
    {
        DomainProviderManager::forgetTldCaches();
    }

    public function invalidateCheapestRetailForAllDomainProducts(): void
    {
        PlatformProduct::query()
            ->where('slug', config('domains.registration_product_slug', 'domain-registration'))
            ->pluck('id')
            ->each(fn (int $id) => Cache::forget('domain.cheapest_retail.'.$id));
    }

    public function invalidateAllDomainPricingCaches(): void
    {
        $this->invalidateTldAndPricing();
        $this->invalidateCheapestRetailForAllDomainProducts();
    }
}
