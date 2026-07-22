<?php

namespace App\Support;

class FaqNormalizer
{
    /**
     * @param  mixed  $items
     * @return list<array{q: string, a: string, open: bool}>|null
     */
    public static function fromRequest(mixed $items): ?array
    {
        if (! is_array($items)) {
            return null;
        }

        $faqs = [];
        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }
            $q = trim((string) ($row['q'] ?? ''));
            $a = trim((string) ($row['a'] ?? ''));
            if ($q === '' && $a === '') {
                continue;
            }
            $faqs[] = [
                'q' => $q,
                'a' => $a,
                'open' => filter_var($row['open'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return $faqs === [] ? null : $faqs;
    }

    /**
     * @param  mixed  $items
     * @return list<string>|null
     */
    public static function stringList(mixed $items): ?array
    {
        if (is_string($items)) {
            $items = preg_split('/\r\n|\r|\n/', $items) ?: [];
        }
        if (! is_array($items)) {
            return null;
        }

        $lines = collect($items)
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        return $lines === [] ? null : $lines;
    }
}
