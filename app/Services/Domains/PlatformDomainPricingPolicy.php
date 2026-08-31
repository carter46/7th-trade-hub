<?php

namespace App\Services\Domains;

use App\Models\PlatformProduct;
use App\Services\Domains\Exceptions\DomainBusinessException;

class PlatformDomainPricingPolicy
{
    private const FX_SCALE = 4;

    private const RETAIL_SCALE = 2;

    /**
     * @return array{retail_price: string, retail_currency: string, markup_percent: float, usd_ngn_rate: float|null}
     */
    public function retailFromProviderCost(float|string $providerCost, string $providerCurrency, PlatformProduct $product): array
    {
        $cost = $this->normalizeCost($providerCost);

        if (bccomp($cost, '0', self::FX_SCALE) <= 0) {
            throw new DomainBusinessException('Invalid provider cost.');
        }

        $meta = $product->meta ?? [];
        $markupRaw = $meta['domain_markup_percent'] ?? 0;
        if (! is_numeric($markupRaw)) {
            throw new DomainBusinessException('Invalid domain markup configuration.');
        }

        $markup = (string) $markupRaw;
        if (bccomp($markup, '0', self::FX_SCALE) < 0) {
            throw new DomainBusinessException('Invalid domain markup configuration.');
        }

        $fx = $meta['domain_fx_policy'] ?? [];
        $usdNgn = isset($fx['usd_ngn_rate']) ? (string) $fx['usd_ngn_rate'] : '0';

        $ngnCost = match (strtoupper($providerCurrency)) {
            'NGN' => $cost,
            'USD' => bccomp($usdNgn, '0', self::FX_SCALE) > 0
                ? bcmul($cost, $usdNgn, self::FX_SCALE)
                : throw new DomainBusinessException('USD to NGN rate is not configured.'),
            default => throw new DomainBusinessException('Unsupported provider currency.'),
        };

        $markupFactor = bcadd('1', bcdiv($markup, '100', self::FX_SCALE), self::FX_SCALE);
        $retailExact = bcmul($ngnCost, $markupFactor, self::FX_SCALE);
        $retail = $this->ceilToWholeNgn($retailExact);

        return [
            'retail_price' => number_format((float) $retail, self::RETAIL_SCALE, '.', ''),
            'retail_currency' => 'NGN',
            'markup_percent' => (float) $markup,
            'usd_ngn_rate' => bccomp($usdNgn, '0', self::FX_SCALE) > 0 ? (float) $usdNgn : null,
        ];
    }

    public function priceDriftPercent(string $quotedRetail, string $currentRetail): string
    {
        if (bccomp($quotedRetail, '0', self::RETAIL_SCALE) <= 0) {
            return '100';
        }

        $diff = bcsub($currentRetail, $quotedRetail, self::RETAIL_SCALE);
        if (bccomp($diff, '0', self::RETAIL_SCALE) < 0) {
            $diff = bcmul($diff, '-1', self::RETAIL_SCALE);
        }

        return bcmul(bcdiv($diff, $quotedRetail, self::FX_SCALE), '100', self::FX_SCALE);
    }

    public function driftWithinTolerance(string $quotedRetail, string $currentRetail, float|string $tolerancePercent): bool
    {
        $drift = $this->priceDriftPercent($quotedRetail, $currentRetail);

        return bccomp($drift, (string) $tolerancePercent, self::FX_SCALE) <= 0;
    }

    private function normalizeCost(float|string $providerCost): string
    {
        if (is_float($providerCost) && (! is_finite($providerCost) || $providerCost <= 0)) {
            throw new DomainBusinessException('Invalid provider cost.');
        }

        return number_format((float) $providerCost, self::FX_SCALE, '.', '');
    }

    private function ceilToWholeNgn(string $amount): string
    {
        if (preg_match('/^(\d+)\.(\d*)$/', $amount, $matches)) {
            $whole = $matches[1];
            $fraction = rtrim($matches[2], '0');

            if ($fraction === '' || $fraction === '0') {
                return $whole.'.00';
            }

            return bcadd($whole, '1', 0).'.00';
        }

        return number_format(ceil((float) $amount), self::RETAIL_SCALE, '.', '');
    }
}
