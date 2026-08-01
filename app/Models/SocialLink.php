<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $fillable = [
        'platform',
        'url',
        'icon',
        'icon_media_id',
        'enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'sort_order' => 'integer',
            'icon_media_id' => 'integer',
        ];
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true)->orderBy('sort_order')->orderBy('id');
    }
}
