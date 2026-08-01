<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TrackingScript extends Model
{
    public const LOCATION_HEAD = 'head';

    public const LOCATION_BODY_START = 'body_start';

    public const LOCATION_BODY_END = 'body_end';

    public const LOCATIONS = [
        self::LOCATION_HEAD,
        self::LOCATION_BODY_START,
        self::LOCATION_BODY_END,
    ];

    protected $fillable = [
        'name',
        'location',
        'enabled',
        'code',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', $location)->orderBy('sort_order')->orderBy('id');
    }
}
