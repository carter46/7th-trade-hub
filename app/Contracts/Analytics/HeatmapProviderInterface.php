<?php

namespace App\Contracts\Analytics;

interface HeatmapProviderInterface
{
    public function isEnabled(): bool;

    public function script(): ?string;
}
