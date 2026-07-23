<?php

namespace App\Contracts\Analytics;

use App\Models\User;

interface AnalyticsServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getOverview(?User $user): array;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getReport(string $section, array $filters, ?User $user): array;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{range: string, from: string, to: string, days: int}
     */
    public function parseRange(array $filters): array;

    /**
     * @return list<array{key: string, label: string, labels: list<string>, values: list<float|null>}>
     */
    public function chartStrip(?User $user, int $days = 7): array;

    /**
     * @return list<string>
     */
    public function allowedSections(User $user): array;
}
