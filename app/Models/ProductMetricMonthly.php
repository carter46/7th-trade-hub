<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMetricMonthly extends Model
{
    protected $table = 'product_metric_monthly';

    protected $fillable = [
        'month',
        'metric_key',
        'dimension',
        'count',
    ];

    protected function casts(): array
    {
        return [
            'count' => 'integer',
        ];
    }
}
