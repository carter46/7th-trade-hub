<?php

namespace App\Support\Domains;

use InvalidArgumentException;

final class DomainFqdn
{
    /**
     * @return array{sld: string, tld: string, fqdn: string}
     */
    public static function parse(string $sld, string $tld): array
    {
        $sld = strtolower(trim($sld));
        $tld = strtolower(ltrim(trim($tld), '.'));

        if ($sld === '' || $tld === '') {
            throw new InvalidArgumentException('Enter a domain name and extension.');
        }

        if (! preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $sld)) {
            throw new InvalidArgumentException('Domain name contains invalid characters.');
        }

        if (! preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $tld)) {
            throw new InvalidArgumentException('Extension is invalid.');
        }

        return [
            'sld' => $sld,
            'tld' => $tld,
            'fqdn' => $sld.'.'.$tld,
        ];
    }

    public static function normalizeFqdn(string $input): string
    {
        $value = strtolower(trim($input));
        $value = preg_replace('#^https?://#', '', $value) ?? $value;
        $value = rtrim($value, '/');
        $value = explode('/', $value)[0];
        $value = explode('?', $value)[0];
        $value = explode('#', $value)[0];
        $value = rtrim($value, '.');

        if ($value === '' || ! str_contains($value, '.')) {
            throw new InvalidArgumentException('Enter a valid domain name (e.g. example.com).');
        }

        if (! preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $value)) {
            throw new InvalidArgumentException('Domain format is invalid.');
        }

        self::assertApexOnly($value);

        return $value;
    }

    /**
     * @return array{sld: string, tld: string, fqdn: string}
     */
    public static function fromFqdn(string $fqdn): array
    {
        $fqdn = self::normalizeFqdn($fqdn);
        $parts = explode('.', $fqdn);
        $tld = array_pop($parts);
        $sld = implode('.', $parts);

        if ($sld === '' || $tld === '') {
            throw new InvalidArgumentException('Domain format is invalid.');
        }

        return ['sld' => $sld, 'tld' => $tld, 'fqdn' => $fqdn];
    }

    private static function assertApexOnly(string $fqdn): void
    {
        if (substr_count($fqdn, '.') !== 1) {
            throw new InvalidArgumentException('Only apex domains (e.g. example.com) can be registered.');
        }
    }
}
