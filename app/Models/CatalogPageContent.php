<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogPageContent extends Model
{
    protected $fillable = [
        'scope',
        'key',
        'banner_image',
        'card_image',
        'banner_media_id',
        'card_media_id',
        'short_description',
        'hero_title',
        'hero_subtitle',
        'benefits',
        'faq',
    ];

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'faq' => 'array',
        ];
    }

    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'banner_media_id');
    }

    public function cardMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'card_media_id');
    }
}
