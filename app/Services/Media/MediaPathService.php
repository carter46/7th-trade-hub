<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class MediaPathService
{
    /**
     * Relative public path suitable for legacy banner_image / hero_image columns
     * and marketing page-header file checks (e.g. storage/media/...).
     */
    public function legacyPathFromMediaId(?int $mediaId, string $variant = 'medium'): ?string
    {
        if (! $mediaId) {
            return null;
        }

        $asset = MediaAsset::query()->with('variants')->find($mediaId);
        if (! $asset) {
            return null;
        }

        return $asset->variantStoragePath($variant)
            ?? $asset->legacyPublicPath($variant);
    }

    /**
     * Prefer MediaAsset URL; fall back to a legacy path or absolute URL string.
     */
    public function resolveUrl(?MediaAsset $asset, ?string $legacyPath = null, string $variant = 'medium'): ?string
    {
        if ($asset) {
            $url = $asset->url($variant) ?? $asset->thumbnailUrl();
            if ($url) {
                return $url;
            }
        }

        return $this->urlFromLegacyPath($legacyPath);
    }

    public function urlFromLegacyPath(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }

        $relative = ltrim(str_replace('\\', '/', $path), '/');

        if (is_file(public_path($relative))) {
            return asset($relative);
        }

        // Storage symlink paths that may not be visible to is_file in some deploys
        if (str_starts_with($relative, 'storage/')) {
            return asset($relative);
        }

        return asset($relative);
    }

    /**
     * Validation rule that rejects soft-deleted media assets.
     */
    public function existsRule(): Exists
    {
        return Rule::exists('media_assets', 'id')->whereNull('deleted_at');
    }
}
