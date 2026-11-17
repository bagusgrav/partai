<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EconomicIndicatorObservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'indicator_name',
        'region_code',
        'region_name',
        'period_year',
        'period_month',
        'value',
        'unit',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'period_month' => 'integer',
            'value' => 'float',
        ];
    }
}
