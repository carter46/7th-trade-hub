<?php

namespace App\Services\Reporting;

use Carbon\Carbon;
use InvalidArgumentException;

class ReportingRange
{
    public const PRESETS = [
        'today',
        '24h',
        '7d',
        '30d',
        '90d',
        'this_month',
        'last_month',
        'custom',
    ];

    public function __construct(
        public readonly string $key,
        public readonly Carbon $from,
        public readonly Carbon $to,
    ) {}

    /**
     * @param  array{range?: string, from?: string|null, to?: string|null}  $input
     */
    public static function fromInput(array $input, string $default = '7d'): self
    {
        $key = (string) ($input['range'] ?? $default);
        if (! in_array($key, self::PRESETS, true)) {
            $key = $default;
        }

        if ($key === 'custom') {
            $from = Carbon::parse($input['from'] ?? now()->subDays(6))->startOfDay();
            $to = Carbon::parse($input['to'] ?? now())->endOfDay();
            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return new self('custom', $from, $to);
        }

        return self::preset($key);
    }

    public static function preset(string $key): self
    {
        [$from, $to] = match ($key) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            '24h' => [now()->subDay(), now()],
            '7d' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            '30d' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            '90d' => [now()->subDays(89)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfDay()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            default => throw new InvalidArgumentException("Unknown reporting range [{$key}]"),
        };

        return new self($key, $from, $to);
    }

    public function days(): int
    {
        return max(1, $this->from->copy()->startOfDay()->diffInDays($this->to->copy()->startOfDay()) + 1);
    }

    public function priorPeriod(): self
    {
        $seconds = max(1, $this->to->diffInSeconds($this->from));
        $priorTo = $this->from->copy()->subSecond();
        $priorFrom = $priorTo->copy()->subSeconds($seconds);

        return new self('prior', $priorFrom, $priorTo);
    }

    /**
     * @return array{range: string, from: string, to: string, days: int}
     */
    public function toArray(): array
    {
        return [
            'range' => $this->key,
            'from' => $this->from->toDateTimeString(),
            'to' => $this->to->toDateTimeString(),
            'days' => $this->days(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function presetOptions(): array
    {
        return [
            ['value' => 'today', 'label' => 'Today'],
            ['value' => '24h', 'label' => '24h'],
            ['value' => '7d', 'label' => '7d'],
            ['value' => '30d', 'label' => '30d'],
            ['value' => '90d', 'label' => '90d'],
            ['value' => 'this_month', 'label' => 'This month'],
            ['value' => 'last_month', 'label' => 'Last month'],
            ['value' => 'custom', 'label' => 'Custom'],
        ];
    }
}
