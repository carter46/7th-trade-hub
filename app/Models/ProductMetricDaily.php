<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMetricDaily extends Model
{
    protected $table = 'product_metric_daily';

    protected $fillable = [
        'day',
        'metric_key',
        'dimension',
        'count',
    ];

    protected function casts(): array
    {
        return [
            'day' => 'date',
            'count' => 'integer',
        ];
    }
}
