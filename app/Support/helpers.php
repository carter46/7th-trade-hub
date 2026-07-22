<?php

use App\Models\MediaAsset;
use App\Services\Media\MediaPathService;

if (! function_exists('media_url')) {
    /**
     * Resolve a public media URL from a MediaAsset and/or legacy path.
     */
    function media_url(?MediaAsset $asset = null, ?string $legacyPath = null, string $variant = 'medium'): ?string
    {
        return app(MediaPathService::class)->resolveUrl($asset, $legacyPath, $variant);
    }
}

if (! function_exists('media_url_from_id')) {
    function media_url_from_id(?int $mediaId, ?string $legacyPath = null, string $variant = 'medium'): ?string
    {
        if (! $mediaId) {
            return app(MediaPathService::class)->urlFromLegacyPath($legacyPath);
        }

        $asset = MediaAsset::query()->with('variants')->find($mediaId);

        return app(MediaPathService::class)->resolveUrl($asset, $legacyPath, $variant);
    }
}
