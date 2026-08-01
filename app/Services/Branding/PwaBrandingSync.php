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
     * Write favicon + PWA icon files from uploaded branding and refresh manifest.json.
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
     * @return array{version: string, favicon: string, apple: string, icon192: string, icon512: string}
     */
    public function publishedUrls(): array
    {
        $version = $this->assetVersion();

        return [
            'version' => $version,
            'favicon' => asset('favicon-32x32.png').'?v='.$version,
            'favicon16' => asset('favicon-16x16.png').'?v='.$version,
            'apple' => asset('apple-touch-icon.png').'?v='.$version,
            'icon192' => asset('icons/icon-192x192.png').'?v='.$version,
            'icon512' => asset('icons/icon-512x512.png').'?v='.$version,
            'manifest' => asset('manifest.json').'?v='.$version,
        ];
    }

    public function iconsExist(): bool
    {
        return is_file(public_path('icons/icon-512x512.png'))
            && is_file(public_path('apple-touch-icon.png'))
            && is_file(public_path('favicon-32x32.png'));
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function syncIcons(array $branding): void
    {
        $sourcePath = $this->resolveSourcePath($branding);

        $iconsDir = public_path('icons');
        if (! File::isDirectory($iconsDir)) {
            File::makeDirectory($iconsDir, 0755, true);
        }

        $bg = $this->backgroundRgb();

        if ($sourcePath) {
            $this->writeSquarePng($sourcePath, 512, $iconsDir.DIRECTORY_SEPARATOR.'icon-512x512.png', $bg);
            $this->writeSquarePng($sourcePath, 192, $iconsDir.DIRECTORY_SEPARATOR.'icon-192x192.png', $bg);
            $this->writeSquarePng($sourcePath, 180, public_path('apple-touch-icon.png'), $bg);
            $this->writeSquarePng($sourcePath, 32, public_path('favicon-32x32.png'), $bg);
            $this->writeSquarePng($sourcePath, 16, public_path('favicon-16x16.png'), $bg);
        } else {
            $this->writeFallbackSquare(512, $iconsDir.DIRECTORY_SEPARATOR.'icon-512x512.png', $bg);
            $this->writeFallbackSquare(192, $iconsDir.DIRECTORY_SEPARATOR.'icon-192x192.png', $bg);
            $this->writeFallbackSquare(180, public_path('apple-touch-icon.png'), $bg);
            $this->writeFallbackSquare(32, public_path('favicon-32x32.png'), $bg);
            $this->writeFallbackSquare(16, public_path('favicon-16x16.png'), $bg);
        }

        // Package @PwaHead falls back to logo.png — keep it in sync.
        File::copy(
            $iconsDir.DIRECTORY_SEPARATOR.'icon-192x192.png',
            public_path('logo.png')
        );

        // Browsers still request /favicon.ico; serve the 32px PNG bytes there.
        File::copy(public_path('favicon-32x32.png'), public_path('favicon.ico'));
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function resolveSourcePath(array $branding): ?string
    {
        $mediaId = $branding['favicon_media_id']
            ?? $branding['logo_light_media_id']
            ?? $branding['logo_dark_media_id']
            ?? null;

        if (! $mediaId) {
            return null;
        }

        $asset = MediaAsset::query()->with('variants')->find((int) $mediaId);
        if (! $asset) {
            return null;
        }

        $sourcePath = $this->absolutePath($asset);

        return ($sourcePath && is_readable($sourcePath)) ? $sourcePath : null;
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

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function backgroundRgb(): array
    {
        $hex = ltrim((string) config('pwa.manifest.theme_color', '#0B6A39'), '#');
        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return [11, 106, 57];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $bg
     */
    private function writeSquarePng(string $sourcePath, int $size, string $destination, array $bg): void
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
        $scale = min($size / max(1, $origW), $size / max(1, $origH));
        $dstW = max(1, (int) round($origW * $scale));
        $dstH = max(1, (int) round($origH * $scale));
        $dstX = (int) max(0, ($size - $dstW) / 2);
        $dstY = (int) max(0, ($size - $dstH) / 2);

        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, false);
        $fill = imagecolorallocate($canvas, $bg[0], $bg[1], $bg[2]);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $fill);

        imagecopyresampled($canvas, $source, $dstX, $dstY, 0, 0, $dstW, $dstH, $origW, $origH);

        if (! imagepng($canvas, $destination, 6)) {
            imagedestroy($source);
            imagedestroy($canvas);
            throw new \RuntimeException('Unable to write branding icon: '.$destination);
        }

        imagedestroy($source);
        imagedestroy($canvas);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $bg
     */
    private function writeFallbackSquare(int $size, string $destination, array $bg): void
    {
        $canvas = imagecreatetruecolor($size, $size);
        $fill = imagecolorallocate($canvas, $bg[0], $bg[1], $bg[2]);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $fill);

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $font = 5;
        $text = '7';
        $tw = imagefontwidth($font) * strlen($text);
        $th = imagefontheight($font);
        imagestring($canvas, $font, (int) (($size - $tw) / 2), (int) (($size - $th) / 2), $text, $white);

        if (! imagepng($canvas, $destination, 6)) {
            imagedestroy($canvas);
            throw new \RuntimeException('Unable to write fallback branding icon: '.$destination);
        }

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

        $version = $this->assetVersion();

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
                [
                    'src' => '/apple-touch-icon.png?v='.$version,
                    'sizes' => '180x180',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
            ],
        ]);

        config(['pwa.manifest' => $manifest]);
        app(PWAService::class)->createOrUpdate($manifest);
    }

    private function assetVersion(): string
    {
        $path = public_path('icons/icon-512x512.png');

        return (string) (is_file($path) ? filemtime($path) : time());
    }
}
