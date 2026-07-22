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

        return Storage::disk($this->disk)->url($path);
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
        $url = $this->url($variant);
        if (! $url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        return $path ? ltrim($path, '/') : $url;
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
}
