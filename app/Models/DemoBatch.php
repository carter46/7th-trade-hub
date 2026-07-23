<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemoBatch extends Model
{
    protected $fillable = [
        'name',
        'source',
        'cleared_at',
    ];

    protected function casts(): array
    {
        return [
            'cleared_at' => 'datetime',
        ];
    }

    public function records(): HasMany
    {
        return $this->hasMany(DemoBatchRecord::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('cleared_at');
    }
}
