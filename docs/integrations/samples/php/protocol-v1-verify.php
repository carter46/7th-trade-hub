<?php
/**
 * Protocol v1 HMAC (reference — must match Hub ProtocolV1Signer).
 * Use to verify Hub→site health/sync, and to sign site→Hub credential sync.
 */

declare(strict_types=1);

function seventh_tradehub_canonicalize(mixed $value): string
{
    if (is_array($value)) {
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return '['.implode(',', array_map('seventh_tradehub_canonicalize', $value)).']';
        }
        ksort($value);
        $parts = [];
        foreach ($value as $key => $item) {
            $parts[] = seventh_tradehub_canonicalize((string) $key).':'.seventh_tradehub_canonicalize($item);
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
        throw new InvalidArgumentException('Unsupported type for canonicalization.');
    }

    return '"'.addcslashes($value, "\\\"\n\r\t").'"';
}

function seventh_tradehub_verify(array $payload, string $clientSecret): bool
{
    $signature = $payload['signature'] ?? null;
    if (! is_string($signature) || $signature === '') {
        return false;
    }

    if (($payload['protocol'] ?? null) !== '7th-tradehub') {
        return false;
    }

    if ((int) ($payload['version'] ?? 0) !== 1) {
        return false;
    }

    $copy = $payload;
    unset($copy['signature']);
    ksort($copy);

    $expected = hash_hmac('sha256', seventh_tradehub_canonicalize($copy), $clientSecret);

    return hash_equals($expected, $signature);
}

function seventh_tradehub_sign(array $payload, string $clientSecret): array
{
    $payload['protocol'] = '7th-tradehub';
    $payload['version'] = 1;
    unset($payload['signature']);
    ksort($payload);
    $payload['signature'] = hash_hmac('sha256', seventh_tradehub_canonicalize($payload), $clientSecret);

    return $payload;
}
