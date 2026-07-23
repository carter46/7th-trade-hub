<?php

namespace Database\Seeders\Demo\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DemoTimeline
{
    public function __construct(private Carbon $anchor)
    {
        $this->anchor = $anchor->copy()->startOfDay();
    }

    public static function fromNow(): self
    {
        return new self(now());
    }

    /** Months before anchor (0 = this month). */
    public function monthsAgo(int $months, int $dayOfMonth = 12, int $hour = 10): Carbon
    {
        return $this->anchor->copy()
            ->subMonthsNoOverflow($months)
            ->day(min($dayOfMonth, 28))
            ->setTime($hour, 15, 0);
    }

    public function daysAgo(int $days, int $hour = 11): Carbon
    {
        return $this->anchor->copy()->subDays($days)->setTime($hour, 20, 0);
    }

    public function stamp(Model $model, Carbon $at, array $extra = []): void
    {
        $payload = array_merge([
            'created_at' => $at,
            'updated_at' => $at,
        ], $extra);

        $model->forceFill($payload)->saveQuietly();
    }
}
