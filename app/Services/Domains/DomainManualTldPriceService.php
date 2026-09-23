<?php

namespace App\Services\Domains;

use App\Models\DomainManualTldPrice;
use App\Models\PlatformProduct;
use App\Support\Domains\DomainProductTldPolicy;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class DomainManualTldPriceService
{
    /**
     * @return Collection<string, DomainManualTldPrice> keyed by normalized tld
     */
    public function pricesForProduct(PlatformProduct $product, bool $activeOnly = true): Collection
    {
        $query = DomainManualTldPrice::query()
            ->where('platform_product_id', $product->id);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get()->keyBy('tld');
    }

    public function activePrice(PlatformProduct $product, string $tld): ?DomainManualTldPrice
    {
        $tld = DomainProductTldPolicy::normalizeList([$tld])[0] ?? '';
        if ($tld === '') {
            return null;
        }

        return DomainManualTldPrice::query()
            ->where('platform_product_id', $product->id)
            ->where('tld', $tld)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @param  list<string>  $allowedTlds
     * @param  array<string, mixed>  $rawPrices  tld => price
     */
    public function syncForProduct(PlatformProduct $product, array $allowedTlds, array $rawPrices): void
    {
        $allowed = DomainProductTldPolicy::normalizeList($allowedTlds);
        if ($allowed === []) {
            throw ValidationException::withMessages([
                'allowed_tlds' => 'Select at least one allowed extension.',
            ]);
        }

        $errors = [];
        $rows = [];

        foreach ($allowed as $tld) {
            if (! $this->isValidTldShape($tld)) {
                $errors["manual_tld_prices.{$tld}"] = "Invalid extension .{$tld}.";
                continue;
            }

            $raw = $rawPrices[$tld] ?? null;
            if ($raw === null || $raw === '') {
                $errors["manual_tld_prices.{$tld}"] = "Enter a price for .{$tld}.";
                continue;
            }

            if (! is_numeric($raw) || (float) $raw <= 0) {
                $errors["manual_tld_prices.{$tld}"] = "Price for .{$tld} must be a positive NGN amount.";
                continue;
            }

            $rows[$tld] = number_format((float) $raw, 2, '.', '');
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $keep = array_keys($rows);

        DomainManualTldPrice::query()
            ->where('platform_product_id', $product->id)
            ->whereNotIn('tld', $keep)
            ->update(['is_active' => false]);

        foreach ($rows as $tld => $price) {
            DomainManualTldPrice::query()->updateOrCreate(
                [
                    'platform_product_id' => $product->id,
                    'tld' => $tld,
                ],
                [
                    'retail_price' => $price,
                    'currency' => 'NGN',
                    'is_active' => true,
                ]
            );
        }
    }

    public function requireActivePrice(PlatformProduct $product, string $tld): DomainManualTldPrice
    {
        $row = $this->activePrice($product, $tld);
        if (! $row) {
            throw new InvalidArgumentException('Selected extension is not available for purchase.');
        }

        if (! DomainProductTldPolicy::isAllowed($product, $row->tld)) {
            throw new InvalidArgumentException('Selected extension is not available for this product.');
        }

        return $row;
    }

    public function isValidTldShape(string $tld): bool
    {
        $tld = ltrim(strtolower(trim($tld)), '.');

        return $tld !== ''
            && strlen($tld) <= 63
            && (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*(?:\.[a-z0-9]+(?:-[a-z0-9]+)*)*$/', $tld);
    }
}
