<?php

namespace App\Modules\Wallet\Services;

use App\Modules\Wallet\Payments\Contracts\PaymentRailInterface;

class BankCatalogService
{
    public function __construct(
        private PaymentRailInterface $rail,
    ) {}

    /**
     * Banks allowed for user selection (filtered from Monnify list).
     *
     * @return list<array{name: string, code: string}>
     */
    public function allowedBanks(): array
    {
        if (! $this->rail->isConfigured()) {
            return [];
        }

        try {
            $banks = $this->rail->listBanks();
        } catch (\Throwable) {
            return [];
        }

        return $this->filterAllowed($banks);
    }

    /**
     * @param  list<array{name: string, code: string}>  $banks
     * @return list<array{name: string, code: string}>
     */
    public function filterAllowed(array $banks): array
    {
        $allow = collect(config('wallet.allowed_bank_patterns', []))
            ->map(fn ($p) => mb_strtolower(trim((string) $p)))
            ->filter()
            ->values()
            ->all();

        $deny = collect(config('wallet.excluded_bank_patterns', []))
            ->map(fn ($p) => mb_strtolower(trim((string) $p)))
            ->filter()
            ->values()
            ->all();

        return collect($banks)
            ->filter(function (array $bank) use ($allow, $deny) {
                $name = mb_strtolower(trim($bank['name'] ?? ''));

                if ($name === '' || ($bank['code'] ?? '') === '') {
                    return false;
                }

                foreach ($deny as $pattern) {
                    if ($pattern !== '' && str_contains($name, $pattern)) {
                        return false;
                    }
                }

                foreach ($allow as $pattern) {
                    if ($pattern !== '' && str_contains($name, $pattern)) {
                        return true;
                    }
                }

                return false;
            })
            ->unique('code')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }
}
