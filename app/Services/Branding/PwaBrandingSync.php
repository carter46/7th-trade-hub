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
    public function shouldRegenerateIcons(array $branding): bool
    {
        if (! $this->iconsExist()) {
            return true;
        }

        $mediaId = (int) ($branding['favicon_media_id']
            ?? $branding['logo_light_media_id']
            ?? $branding['logo_dark_media_id']
            ?? 0);

        if ($mediaId <= 0) {
            return false;
        }

        $iconMtime = @filemtime(public_path('icons/icon-512x512.png'));
        if (! $iconMtime) {
            return true;
        }

        $asset = MediaAsset::query()->find($mediaId);

        return $asset?->updated_at?->getTimestamp() > $iconMtime;
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
        } elseif ($this->brandingMediaIds($branding) !== []) {
            throw new \RuntimeException(
                'Branding media could not be read for favicon/PWA icon sync.'
            );
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
        foreach ($this->brandingMediaIds($branding) as $mediaId) {
            $asset = MediaAsset::query()->with('variants')->find($mediaId);
            if (! $asset) {
                continue;
            }

            $sourcePath = $this->absolutePath($asset);
            if ($sourcePath && is_readable($sourcePath)) {
                return $sourcePath;
            }

            $fetched = $this->sourcePathFromMediaUrl($mediaId);
            if ($fetched) {
                return $fetched;
            }
        }

        foreach (config('pwa.default_icon_paths', []) as $relative) {
            $path = public_path(ltrim((string) $relative, '/'));
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $branding
     * @return list<int>
     */
    private function brandingMediaIds(array $branding): array
    {
        $ids = [];

        foreach (['favicon_media_id', 'logo_light_media_id', 'logo_dark_media_id'] as $key) {
            $id = (int) ($branding[$key] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function sourcePathFromMediaUrl(int $mediaId): ?string
    {
        $url = media_url_from_id($mediaId, null, 'original');
        if (! is_string($url) || $url === '') {
            return null;
        }

        $local = $this->localPathFromPublicUrl($url);
        if ($local && is_readable($local)) {
            return $local;
        }

        $absolute = str_starts_with($url, 'http') ? $url : url($url);
        $bytes = @file_get_contents($absolute);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        $destination = tempnam(sys_get_temp_dir(), 'pwa_icon_');
        if ($destination === false) {
            return null;
        }

        $path = $destination.'.png';
        @unlink($destination);

        if (@file_put_contents($path, $bytes) === false) {
            return null;
        }

        return is_readable($path) ? $path : null;
    }

    private function localPathFromPublicUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (str_starts_with($path, '/storage/')) {
            $relative = ltrim(substr($path, strlen('/storage/')), '/');
            $storagePath = Storage::disk('public')->path($relative);
            if (is_readable($storagePath)) {
                return $storagePath;
            }

            $publicCopy = public_path('storage/'.$relative);
            if (is_readable($publicCopy)) {
                return $publicCopy;
            }
        }

        $publicPath = public_path(ltrim($path, '/'));
        if (is_readable($publicPath)) {
            return $publicPath;
        }

        return null;
    }

    private function absolutePath(MediaAsset $asset): ?string
    {
        $asset->loadMissing('variants');

        // Use raw variant paths for disk I/O. variantStoragePath() prefixes "storage/"
        // for public URLs, which breaks Storage::disk('public')->exists()/path().
        foreach (['original', 'large', 'medium', 'small', 'thumbnail'] as $key) {
            $relative = $asset->variants->firstWhere('key', $key)?->path;
            if (! is_string($relative) || $relative === '') {
                continue;
            }

            $relative = ltrim(str_replace('\\', '/', $relative), '/');
            if (str_starts_with($relative, 'storage/')) {
                $relative = substr($relative, strlen('storage/'));
            }

            $disk = Storage::disk($asset->disk);
            if ($disk->exists($relative)) {
                return $disk->path($relative);
            }

            // Fallback when the public disk symlink is used as the readable path.
            $publicCopy = public_path('storage/'.$relative);
            if (is_readable($publicCopy)) {
                return $publicCopy;
            }
        }

        return null;
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
     * Resize onto a transparent square — do not paint the brand theme color behind uploads.
     * ($bg is unused; kept so call sites stay stable. Fallback icons still use theme color.)
     *
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

        imagealphablending($source, true);
        imagesavealpha($source, true);

        $origW = imagesx($source);
        $origH = imagesy($source);
        $scale = min($size / max(1, $origW), $size / max(1, $origH));
        $dstW = max(1, (int) round($origW * $scale));
        $dstH = max(1, (int) round($origH * $scale));
        $dstX = (int) max(0, ($size - $dstW) / 2);
        $dstY = (int) max(0, ($size - $dstH) / 2);

        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);

        // Blend the source onto the transparent canvas (keeps PNG alpha from the upload).
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $source, $dstX, $dstY, 0, 0, $dstW, $dstH, $origW, $origH);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

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
