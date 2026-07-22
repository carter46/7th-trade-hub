<?php

namespace App\Services\Media;

use App\Enums\MediaType;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class MediaUploadService
{
    public function storeImage(UploadedFile $file, ?User $uploader = null, ?bool $keepOriginal = null): MediaAsset
    {
        if (! MediaType::Image->isEnabled()) {
            throw ValidationException::withMessages([
                'files' => __('Image uploads are not enabled.'),
            ]);
        }

        $this->validateImageUpload($file);

        $checksum = hash_file('sha256', $file->getRealPath());

        $existing = MediaAsset::query()
            ->where('checksum', $checksum)
            ->where('type', MediaType::Image)
            ->first();

        if ($existing) {
            return $existing->loadMissing('variants');
        }

        $keepOriginal ??= (bool) config('media.keep_original', false);
        $disk = (string) config('media.disk', 'public');
        $directory = trim((string) config('media.directory', 'media'), '/');
        $folder = now()->format('Y/m');
        $basePath = $directory.'/'.$folder;
        $ulid = (string) Str::ulid();
        $quality = (int) config('media.webp_quality', 80);
        $derivatives = (array) config('media.derivatives', []);

        $processed = $this->processImage($file->getRealPath(), $derivatives, $quality, $keepOriginal);

        $writtenPaths = [];

        try {
            return DB::transaction(function () use (
                $file,
                $uploader,
                $keepOriginal,
                $disk,
                $folder,
                $basePath,
                $ulid,
                $checksum,
                $processed,
                &$writtenPaths,
            ): MediaAsset {
                $storage = Storage::disk($disk);
                $variantRows = [];

                foreach ($processed['variants'] as $key => $variant) {
                    $path = $basePath.'/'.$ulid.'-'.$key.'.'.$variant['extension'];
                    $binary = $variant['binary'];
                    $sizeBytes = strlen($binary);
                    $storage->put($path, $binary);
                    $writtenPaths[] = $path;
                    unset($binary, $processed['variants'][$key]['binary']);

                    $variantRows[] = [
                        'key' => $key,
                        'path' => $path,
                        'width' => $variant['width'],
                        'height' => $variant['height'],
                        'size_bytes' => $sizeBytes,
                        'mime' => $variant['mime'],
                    ];
                }

                $primary = collect($variantRows)->firstWhere('key', 'medium')
                    ?? collect($variantRows)->firstWhere('key', 'large')
                    ?? $variantRows[0] ?? null;

                $asset = MediaAsset::query()->create([
                    'type' => MediaType::Image,
                    'disk' => $disk,
                    'folder' => $folder,
                    'collection' => config('media.default_collection', 'library'),
                    'brand_key' => config('media.brand_key') ?: null,
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $primary['mime'] ?? 'image/webp',
                    'extension' => pathinfo($primary['path'] ?? 'x.webp', PATHINFO_EXTENSION) ?: 'webp',
                    'size_bytes' => $primary['size_bytes'] ?? 0,
                    'width' => $processed['width'],
                    'height' => $processed['height'],
                    'checksum' => $checksum,
                    'uploaded_by' => $uploader?->id,
                    'keep_original' => $keepOriginal,
                ]);

                foreach ($variantRows as $row) {
                    $asset->variants()->create($row);
                }

                Log::info('media.upload', [
                    'media_asset_id' => $asset->id,
                    'disk' => $disk,
                    'variants' => count($variantRows),
                    'uploader_id' => $uploader?->id,
                ]);

                return $asset->load('variants');
            });
        } catch (\Throwable $e) {
            foreach ($writtenPaths as $path) {
                try {
                    Storage::disk($disk)->delete($path);
                } catch (\Throwable) {
                    // ignore cleanup failures
                }
            }
            throw $e;
        }
    }

    /**
     * Store a private document (deposit proofs, KYC).
     *
     * Always returns path metadata so callers never lose the file when Document
     * media type is enabled. When enabled, also creates/links a MediaAsset.
     *
     * @return array{disk: string, path: string, original_name: string, mime: string, size_bytes: int, media_asset_id: int|null}
     */
    public function storeDocument(UploadedFile $file, ?User $uploader = null): array
    {
        $maxKb = (int) config('media.documents.max_upload_size_kb', 5120);
        $allowedMimes = (array) config('media.documents.allowed_mimes', []);
        $allowedExt = (array) config('media.documents.allowed_extensions', []);

        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');

        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages([
                'file' => __('Document exceeds the maximum size of :kb KB.', ['kb' => $maxKb]),
            ]);
        }

        if ($allowedMimes !== [] && ! in_array($mime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'file' => __('Unsupported document type.'),
            ]);
        }

        if ($allowedExt !== [] && ! in_array($ext, $allowedExt, true)) {
            throw ValidationException::withMessages([
                'file' => __('Unsupported document extension.'),
            ]);
        }

        $disk = (string) config('media.documents.disk', 'local');
        $directory = trim((string) config('media.documents.directory', 'documents'), '/');
        $folder = now()->format('Y/m');
        $filename = (string) Str::ulid().($ext !== '' ? '.'.$ext : '');
        $path = $directory.'/'.$folder.'/'.$filename;

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        $result = [
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => (string) $mime,
            'size_bytes' => (int) $file->getSize(),
            'media_asset_id' => null,
        ];

        if (! MediaType::Document->isEnabled()) {
            return $result;
        }

        $checksum = hash_file('sha256', $file->getRealPath());

        $existing = MediaAsset::query()
            ->where('checksum', $checksum)
            ->where('type', MediaType::Document)
            ->first();

        if ($existing) {
            Storage::disk($disk)->delete($path);
            $existingPath = $existing->variants()->where('key', 'original')->value('path') ?: $path;

            return [
                'disk' => $existing->disk,
                'path' => $existingPath,
                'original_name' => $existing->original_name,
                'mime' => $existing->mime,
                'size_bytes' => (int) $existing->size_bytes,
                'media_asset_id' => $existing->id,
            ];
        }

        $asset = MediaAsset::query()->create([
            'type' => MediaType::Document,
            'disk' => $disk,
            'folder' => $folder,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $mime,
            'extension' => $ext,
            'size_bytes' => (int) $file->getSize(),
            'checksum' => $checksum,
            'uploaded_by' => $uploader?->id,
            'keep_original' => true,
        ]);

        $asset->variants()->create([
            'key' => 'original',
            'path' => $path,
            'size_bytes' => (int) $file->getSize(),
            'mime' => $mime,
        ]);

        $result['media_asset_id'] = $asset->id;

        return $result;
    }

    protected function validateImageUpload(UploadedFile $file): void
    {
        $maxKb = (int) config('media.max_upload_size_kb', 2048);
        $allowedMimes = (array) config('media.allowed_mimes', []);
        $allowedExt = (array) config('media.allowed_extensions', []);
        $maxWidth = (int) config('media.max_dimensions.width', 8000);
        $maxHeight = (int) config('media.max_dimensions.height', 8000);

        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages([
                'files' => __('Image exceeds the maximum size of :kb KB.', ['kb' => $maxKb]),
            ]);
        }

        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');

        if ($allowedMimes !== [] && ! in_array($mime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'files' => __('Unsupported image type.'),
            ]);
        }

        if ($allowedExt !== [] && ! in_array($ext, $allowedExt, true)) {
            throw ValidationException::withMessages([
                'files' => __('Unsupported image extension.'),
            ]);
        }

        $size = @getimagesize($file->getRealPath());
        if ($size === false) {
            throw ValidationException::withMessages([
                'files' => __('Unable to read image dimensions.'),
            ]);
        }

        [$width, $height] = $size;

        if ($width > $maxWidth || $height > $maxHeight) {
            throw ValidationException::withMessages([
                'files' => __('Image dimensions must not exceed :w×:h.', [
                    'w' => $maxWidth,
                    'h' => $maxHeight,
                ]),
            ]);
        }
    }

    /**
     * @param  array<string, array{max_width?: int, max_height?: int, crop?: bool}>  $derivatives
     * @return array{width: int, height: int, variants: array<string, array{binary: string, width: int, height: int, mime: string, extension: string, size?: int}>}
     */
    protected function processImage(string $path, array $derivatives, int $quality, bool $keepOriginal): array
    {
        if (class_exists(ImageManager::class) && extension_loaded('gd')) {
            return $this->processWithIntervention($path, $derivatives, $quality, $keepOriginal);
        }

        if (extension_loaded('gd')) {
            return $this->processWithGd($path, $derivatives, $quality, $keepOriginal);
        }

        throw ValidationException::withMessages([
            'files' => __('Image processing is unavailable on this server.'),
        ]);
    }

    /**
     * @param  array<string, array{max_width?: int, max_height?: int, crop?: bool}>  $derivatives
     * @return array{width: int, height: int, variants: array<string, array{binary: string, width: int, height: int, mime: string, extension: string}>}
     */
    protected function processWithIntervention(string $path, array $derivatives, int $quality, bool $keepOriginal): array
    {
        $manager = new ImageManager(new GdDriver);
        $source = $manager->read($path);
        $origWidth = $source->width();
        $origHeight = $source->height();
        $variants = [];

        if ($keepOriginal && ! (bool) config('media.strip_exif', true)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');
            $binary = (string) file_get_contents($path);
            $variants['original'] = [
                'binary' => $binary,
                'width' => $origWidth,
                'height' => $origHeight,
                'mime' => mime_content_type($path) ?: 'image/jpeg',
                'extension' => $ext === 'jpeg' ? 'jpg' : $ext,
            ];
        } elseif ($keepOriginal) {
            // Re-encode to strip EXIF while preserving a large derivative as "original".
            $encoded = $source->toJpeg((int) config('media.jpeg_quality', 82));
            $variants['original'] = [
                'binary' => (string) $encoded,
                'width' => $origWidth,
                'height' => $origHeight,
                'mime' => 'image/jpeg',
                'extension' => 'jpg',
            ];
        }

        foreach ($derivatives as $key => $config) {
            $image = $manager->read($path);
            $maxWidth = isset($config['max_width']) ? (int) $config['max_width'] : null;
            $maxHeight = isset($config['max_height']) ? (int) $config['max_height'] : null;
            $crop = (bool) ($config['crop'] ?? false);

            if ($crop && $maxWidth && $maxHeight) {
                $image->cover($maxWidth, $maxHeight);
            } else {
                $image->scaleDown(width: $maxWidth, height: $maxHeight);
            }

            $encoded = $image->toWebp($quality);
            $variants[$key] = [
                'binary' => (string) $encoded,
                'width' => $image->width(),
                'height' => $image->height(),
                'mime' => 'image/webp',
                'extension' => 'webp',
            ];
        }

        return [
            'width' => $origWidth,
            'height' => $origHeight,
            'variants' => $variants,
        ];
    }

    /**
     * Minimal GD fallback when Intervention is not available.
     *
     * @param  array<string, array{max_width?: int, max_height?: int, crop?: bool}>  $derivatives
     * @return array{width: int, height: int, variants: array<string, array{binary: string, width: int, height: int, mime: string, extension: string}>}
     */
    protected function processWithGd(string $path, array $derivatives, int $quality, bool $keepOriginal): array
    {
        $info = getimagesize($path);
        if ($info === false) {
            throw ValidationException::withMessages([
                'files' => __('Unable to read image.'),
            ]);
        }

        [$origWidth, $origHeight] = $info;
        $source = $this->gdCreateFromPath($path, $info['mime'] ?? null);
        if ($source === false) {
            throw ValidationException::withMessages([
                'files' => __('Unable to open image for processing.'),
            ]);
        }

        // Re-encode strips EXIF when strip_exif is enabled (default).
        $variants = [];

        if ($keepOriginal) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');
            $variants['original'] = [
                'binary' => (string) file_get_contents($path),
                'width' => $origWidth,
                'height' => $origHeight,
                'mime' => $info['mime'] ?? 'image/jpeg',
                'extension' => $ext === 'jpeg' ? 'jpg' : $ext,
            ];
        }

        foreach ($derivatives as $key => $config) {
            $maxWidth = isset($config['max_width']) ? (int) $config['max_width'] : null;
            $maxHeight = isset($config['max_height']) ? (int) $config['max_height'] : null;
            $crop = (bool) ($config['crop'] ?? false);

            $resized = $this->gdResize($source, $origWidth, $origHeight, $maxWidth, $maxHeight, $crop);
            $binary = $this->gdEncode($resized['resource'], 'webp', $quality);

            if ($resized['resource'] !== $source) {
                imagedestroy($resized['resource']);
            }

            $variants[$key] = [
                'binary' => $binary,
                'width' => $resized['width'],
                'height' => $resized['height'],
                'mime' => 'image/webp',
                'extension' => 'webp',
            ];
        }

        imagedestroy($source);

        return [
            'width' => $origWidth,
            'height' => $origHeight,
            'variants' => $variants,
        ];
    }

    /**
     * @return resource|\GdImage|false
     */
    protected function gdCreateFromPath(string $path, ?string $mime)
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            'image/gif' => @imagecreatefromgif($path),
            default => @imagecreatefromstring((string) file_get_contents($path)),
        };
    }

    /**
     * @param  resource|\GdImage  $source
     * @return array{resource: resource|\GdImage, width: int, height: int}
     */
    protected function gdResize($source, int $origWidth, int $origHeight, ?int $maxWidth, ?int $maxHeight, bool $crop): array
    {
        if ($crop && $maxWidth && $maxHeight) {
            $targetW = $maxWidth;
            $targetH = $maxHeight;
            $scale = max($targetW / $origWidth, $targetH / $origHeight);
            $srcW = (int) round($targetW / $scale);
            $srcH = (int) round($targetH / $scale);
            $srcX = (int) max(0, ($origWidth - $srcW) / 2);
            $srcY = (int) max(0, ($origHeight - $srcH) / 2);

            $dest = imagecreatetruecolor($targetW, $targetH);
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            imagecopyresampled($dest, $source, 0, 0, $srcX, $srcY, $targetW, $targetH, $srcW, $srcH);

            return ['resource' => $dest, 'width' => $targetW, 'height' => $targetH];
        }

        $ratio = 1.0;
        if ($maxWidth && $origWidth > $maxWidth) {
            $ratio = min($ratio, $maxWidth / $origWidth);
        }
        if ($maxHeight && $origHeight > $maxHeight) {
            $ratio = min($ratio, $maxHeight / $origHeight);
        }

        if ($ratio >= 1.0) {
            return ['resource' => $source, 'width' => $origWidth, 'height' => $origHeight];
        }

        $targetW = max(1, (int) round($origWidth * $ratio));
        $targetH = max(1, (int) round($origHeight * $ratio));
        $dest = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        imagecopyresampled($dest, $source, 0, 0, 0, 0, $targetW, $targetH, $origWidth, $origHeight);

        return ['resource' => $dest, 'width' => $targetW, 'height' => $targetH];
    }

    /**
     * @param  resource|\GdImage  $image
     */
    protected function gdEncode($image, string $format, int $quality): string
    {
        ob_start();

        match (strtolower($format)) {
            'png' => imagepng($image),
            'gif' => imagegif($image),
            'webp' => function_exists('imagewebp')
                ? imagewebp($image, null, $quality)
                : imagejpeg($image, null, $quality),
            default => imagejpeg($image, null, $quality),
        };

        return (string) ob_get_clean();
    }
}
