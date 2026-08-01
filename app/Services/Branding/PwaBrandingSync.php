<?php

namespace App\Services\Branding;

use App\Models\MediaAsset;
use EragLaravelPwa\Services\PWAService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PwaBrandingSync
{
    /**
     * Write PWA icon files from uploaded branding and refresh manifest.json.
     *
     * @param  array<string, mixed>|null  $branding
     */
    public function sync(?array $branding = null): bool
    {
        $branding ??= app(SiteBrandingRepository::class)->all();

        try {
            $this->syncIcons($branding);
            $this->writeManifest($branding);

            return true;
        } catch (Throwable $e) {
            Log::warning('pwa.branding_sync_failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function syncIcons(array $branding): void
    {
        $mediaId = $branding['favicon_media_id']
            ?? $branding['logo_light_media_id']
            ?? $branding['logo_dark_media_id']
            ?? null;

        if (! $mediaId) {
            return;
        }

        $asset = MediaAsset::query()->with('variants')->find((int) $mediaId);
        if (! $asset) {
            return;
        }

        $sourcePath = $this->absolutePath($asset);
        if (! $sourcePath || ! is_readable($sourcePath)) {
            return;
        }

        $iconsDir = public_path('icons');
        if (! File::isDirectory($iconsDir)) {
            File::makeDirectory($iconsDir, 0755, true);
        }

        $this->writeSquarePng($sourcePath, 192, $iconsDir.DIRECTORY_SEPARATOR.'icon-192x192.png');
        $this->writeSquarePng($sourcePath, 512, $iconsDir.DIRECTORY_SEPARATOR.'icon-512x512.png');
    }

    private function absolutePath(MediaAsset $asset): ?string
    {
        $relative = $asset->variantStoragePath('original')
            ?? $asset->variantStoragePath('large')
            ?? $asset->variantStoragePath('medium')
            ?? $asset->variantStoragePath('thumbnail');

        if (! $relative) {
            return null;
        }

        $disk = Storage::disk($asset->disk);
        if (! $disk->exists($relative)) {
            return null;
        }

        return $disk->path($relative);
    }

    private function writeSquarePng(string $sourcePath, int $size, string $destination): void
    {
        $info = @getimagesize($sourcePath);
        if ($info === false) {
            throw new \RuntimeException('Unable to read branding image for PWA icons.');
        }

        $mime = $info['mime'] ?? null;
        $source = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            'image/gif' => @imagecreatefromgif($sourcePath),
            default => @imagecreatefromstring((string) file_get_contents($sourcePath)),
        };

        if ($source === false) {
            throw new \RuntimeException('Unable to open branding image for PWA icons.');
        }

        $origW = imagesx($source);
        $origH = imagesy($source);
        $scale = max($size / max(1, $origW), $size / max(1, $origH));
        $srcW = (int) round($size / $scale);
        $srcH = (int) round($size / $scale);
        $srcX = (int) max(0, ($origW - $srcW) / 2);
        $srcY = (int) max(0, ($origH - $srcH) / 2);

        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);
        imagealphablending($canvas, true);

        imagecopyresampled($canvas, $source, 0, 0, $srcX, $srcY, $size, $size, $srcW, $srcH);

        if (! imagepng($canvas, $destination, 6)) {
            imagedestroy($source);
            imagedestroy($canvas);
            throw new \RuntimeException('Unable to write PWA icon: '.$destination);
        }

        imagedestroy($source);
        imagedestroy($canvas);
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function writeManifest(array $branding): void
    {
        $name = (string) ($branding['site_name'] ?: config('pwa.manifest.name'));
        $short = (string) (($branding['site_short_name'] ?? '') !== ''
            ? $branding['site_short_name']
            : $name);
        $description = (string) (($branding['meta_description'] ?? '') !== ''
            ? $branding['meta_description']
            : config('pwa.manifest.description'));

        $version = (string) (file_exists(public_path('icons/icon-512x512.png'))
            ? filemtime(public_path('icons/icon-512x512.png'))
            : time());

        $manifest = array_merge(config('pwa.manifest', []), [
            'name' => $name,
            'short_name' => $short,
            'description' => $description,
            'icons' => [
                [
                    'src' => '/icons/icon-512x512.png?v='.$version,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/icons/icon-192x192.png?v='.$version,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
            ],
        ]);

        config(['pwa.manifest' => $manifest]);
        app(PWAService::class)->createOrUpdate($manifest);
    }
}
