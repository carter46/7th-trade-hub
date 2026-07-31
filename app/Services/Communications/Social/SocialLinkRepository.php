<?php

namespace App\Services\Communications\Social;

use App\Models\SocialLink;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SocialLinkRepository
{
    public const CACHE_KEY = 'platform.social_links';

    /**
     * @return Collection<int, SocialLink>
     */
    public function enabled(): Collection
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return SocialLink::query()->enabled()->get();
        });
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
