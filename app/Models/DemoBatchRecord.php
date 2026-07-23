<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoBatchRecord extends Model
{
    protected $fillable = [
        'demo_batch_id',
        'record_type',
        'record_id',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(DemoBatch::class, 'demo_batch_id');
    }
}
