<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'type',
        'disk',
        'folder',
        'original_name',
        'mime',
        'extension',
        'size_bytes',
        'width',
        'height',
        'checksum',
        'alt',
        'tags',
        'collection',
        'brand_key',
        'uploaded_by',
        'keep_original',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'keep_original' => 'boolean',
            'tags' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MediaAsset $asset): void {
            if (empty($asset->uuid)) {
                $asset->uuid = (string) Str::uuid();
            }
        });
    }

    public function purgeFiles(): void
    {
        $this->loadMissing('variants');
        $disk = Storage::disk($this->disk);

        foreach ($this->variants as $variant) {
            if ($variant->path) {
                $disk->delete($variant->path);
            }
        }
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(string $variant = 'medium'): ?string
    {
        $path = $this->variants
            ->firstWhere('key', $variant)
            ?->path
            ?? $this->variants->firstWhere('key', 'medium')?->path
            ?? $this->variants->firstWhere('key', 'original')?->path
            ?? $this->variants->first()?->path;

        if (! $path) {
            return null;
        }

        return $this->normalizePublicUrl(Storage::disk($this->disk)->url($path), $path);
    }

    public function thumbnailUrl(): ?string
    {
        return $this->url('thumbnail') ?? $this->url('small') ?? $this->url('medium');
    }

    /**
     * Path suitable for asset() / legacy banner_image-style columns.
     */
    public function legacyPublicPath(string $variant = 'medium'): ?string
    {
        return $this->variantStoragePath($variant);
    }

    public function variantStoragePath(string $variant = 'medium'): ?string
    {
        $this->loadMissing('variants');

        $path = $this->variants->firstWhere('key', $variant)?->path
            ?? $this->variants->firstWhere('key', 'medium')?->path
            ?? $this->variants->first()?->path;

        if (! $path) {
            return null;
        }

        return $this->disk === 'public' ? 'storage/'.$path : $path;
    }

    /**
     * Ensure media URLs are root-absolute (/storage/...) or fully absolute (https://...).
     * Prevents nested admin routes from resolving relative srcs like
     * /admin/.../7th-tradehub.online/storage/...
     */
    protected function normalizePublicUrl(?string $url, ?string $storagePath = null): ?string
    {
        if (! is_string($url) || $url === '') {
            return $storagePath && $this->disk === 'public'
                ? '/storage/'.ltrim($storagePath, '/')
                : null;
        }

        if (preg_match('#^https?://#i', $url) || str_starts_with($url, 'data:')) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return $url;
        }

        // Broken APP_URL without scheme: "example.com/storage/media/..."
        if (preg_match('#(?:^|/)(storage/.+)$#', str_replace('\\', '/', $url), $matches)) {
            return '/'.$matches[1];
        }

        if ($storagePath && $this->disk === 'public') {
            return '/storage/'.ltrim($storagePath, '/');
        }

        return '/'.ltrim(str_replace('\\', '/', $url), '/');
    }
}
