<?php

namespace App\Modules\Wallet\Services;

class CryptoExplorerUrl
{
    public static function forTx(?string $network, ?string $txHash): ?string
    {
        if (! $txHash) {
            return null;
        }

        $key = strtolower(trim((string) $network));
        $template = config('crypto.explorers.'.$key);
        if (! is_string($template) || $template === '') {
            return null;
        }

        return str_replace('{hash}', rawurlencode($txHash), $template);
    }
}
