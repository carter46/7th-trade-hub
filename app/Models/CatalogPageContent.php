<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogPageContent extends Model
{
    protected $fillable = [
        'scope',
        'key',
        'banner_image',
        'card_image',
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
};
