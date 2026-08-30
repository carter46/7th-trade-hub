<?php

namespace App\Services\SiteIntegrations;

use InvalidArgumentException;

/**
 * 7th Trade Hub Integration Protocol v1 signing / verification.
 *
 * Hub signs with the per-integration client_secret.
 * Target site verifies with the same secret provisioned for THAT integration only.
 */
class ProtocolV1Signer
{
    public const PROTOCOL = '7th-tradehub';

    public const VERSION = 1;

    /**
     * @param  array<string, mixed>  $payload  Must include protocol fields except signature
     * @return array<string, mixed>
     */
    public function sign(array $payload, string $clientSecret): array
    {
        $payload['protocol'] = self::PROTOCOL;
        $payload['version'] = self::VERSION;
        unset($payload['signature']);

        $payload['signature'] = $this->computeSignature($payload, $clientSecret);

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verify(array $payload, string $clientSecret): bool
    {
        $signature = $payload['signature'] ?? null;
        if (! is_string($signature) || $signature === '') {
            return false;
        }

        if (($payload['protocol'] ?? null) !== self::PROTOCOL) {
            return false;
        }

        if ((int) ($payload['version'] ?? 0) !== self::VERSION) {
            return false;
        }

        $expected = $this->computeSignature($payload, $clientSecret);

        return hash_equals($expected, $signature);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function computeSignature(array $payload, string $clientSecret): string
    {
        $copy = $payload;
        unset($copy['signature']);
        ksort($copy);

        $canonical = $this->canonicalize($copy);

        return hash_hmac('sha256', $canonical, $clientSecret);
    }

    /**
     * @param  mixed  $value
     */
    private function canonicalize(mixed $value): string
    {
        if (is_array($value)) {
            if ($this->isList($value)) {
                $parts = array_map(fn ($item) => $this->canonicalize($item), $value);

                return '['.implode(',', $parts).']';
            }

            ksort($value);
            $parts = [];
            foreach ($value as $key => $item) {
                $parts[] = $this->canonicalize((string) $key).':'.$this->canonicalize($item);
            }

            return '{'.implode(',', $parts).'}';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException('Unsupported payload type for Protocol v1 canonicalization.');
        }

        return '"'.addcslashes($value, "\\\"\n\r\t").'"';
    }

    /**
     * @param  array<mixed>  $array
     */
    private function isList(array $array): bool
    {
        if ($array === []) {
            return true;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }
}
