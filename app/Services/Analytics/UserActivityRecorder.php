<?php

namespace App\Services\Analytics;

use App\Models\ProductMetricDaily;
use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserActivityRecorder
{
    public function record(
        ?int $userId,
        string $action,
        ?Model $subject = null,
        ?string $contextKey = null,
        array $meta = []
    ): void {
        if (! $userId) {
            return;
        }

        UserActivity::query()->create([
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'context_key' => $contextKey,
            'meta' => $meta ?: null,
            'occurred_at' => now(),
        ]);

        $metricKey = $contextKey ?: ($subject
            ? class_basename($subject).'.'.$action
            : $action);

        $dimension = $subject ? (string) $subject->getKey() : null;

        $this->incrementDaily($metricKey, $dimension);
    }

    public function incrementDaily(string $metricKey, ?string $dimension = null, int $by = 1): void
    {
        $day = now()->toDateString();
        $dimension = $dimension ?? '';

        try {
            $existing = ProductMetricDaily::query()
                ->where('day', $day)
                ->where('metric_key', $metricKey)
                ->where('dimension', $dimension)
                ->first();

            if ($existing) {
                $existing->increment('count', $by);

                return;
            }

            ProductMetricDaily::query()->create([
                'day' => $day,
                'metric_key' => $metricKey,
                'dimension' => $dimension,
                'count' => $by,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            ProductMetricDaily::query()
                ->where('day', $day)
                ->where('metric_key', $metricKey)
                ->where('dimension', $dimension)
                ->increment('count', $by);
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, Model>
     */
    public function recentSubjects(int $userId, string $subjectType, int $limit = 8)
    {
        $ids = UserActivity::query()
            ->where('user_id', $userId)
            ->where('action', 'viewed')
            ->where('subject_type', $subjectType)
            ->orderByDesc('occurred_at')
            ->limit($limit * 3)
            ->pluck('subject_id')
            ->unique()
            ->take($limit)
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        /** @var class-string<Model> $class */
        $class = $subjectType;
        if (! class_exists($class)) {
            return collect();
        }

        return $class::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Model $m) => $ids->search($m->getKey()))
            ->values();
    }
}
