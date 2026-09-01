<?php

namespace App\Modules\Wallet\Payments\Monnify;

/**
 * Maps Monnify disbursement responseBody fields documented for single transfers.
 *
 * @see https://teamapt.atlassian.net/wiki/spaces/MON/pages/223149917
 * @see https://developers.monnify.com/docs/disbursements/single-transfers/
 */
final class MonnifyDisbursementMapper
{
    /** @var list<string> */
    private const ALLOWED_KEYS = [
        'amount',
        'reference',
        'status',
        'dateCreated',
        'totalFee',
        'fee',
        'destinationAccountName',
        'destinationBankName',
        'destinationAccountNumber',
        'destinationBankCode',
        'currency',
        'completedOn',
    ];

    /**
     * @param  array<string, mixed>  $responseBody
     * @return array<string, mixed>
     */
    public static function snapshot(array $responseBody): array
    {
        $out = [];
        foreach (self::ALLOWED_KEYS as $key) {
            if (array_key_exists($key, $responseBody) && $responseBody[$key] !== null && $responseBody[$key] !== '') {
                $out[$key] = $responseBody[$key];
            }
        }

        return $out;
    }

    public static function status(array $responseBody): string
    {
        return strtoupper((string) ($responseBody['status'] ?? ''));
    }

    public static function requiresAuthorization(array $responseBody): bool
    {
        return self::status($responseBody) === 'PENDING_AUTHORIZATION';
    }

    public static function isTerminalFailure(string $status): bool
    {
        return in_array(strtoupper($status), ['FAILED', 'EXPIRED', 'REVERSED'], true);
    }

    public static function isSuccess(string $status): bool
    {
        return in_array(strtoupper($status), ['SUCCESS', 'COMPLETED', 'SUCCESSFUL'], true);
    }

    public static function isInFlight(string $status): bool
    {
        return in_array(strtoupper($status), [
            'PENDING',
            'AWAITING_PROCESSING',
            'IN_PROGRESS',
            'PENDING_AUTHORIZATION',
        ], true);
    }
}
