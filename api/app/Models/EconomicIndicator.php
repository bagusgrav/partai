<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EconomicIndicator extends Model
{
    protected $fillable = [
        'indicator_type',
        'title',
        'value',
        'unit',
        'period',
        'source',
        'trend',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'float',
        ];
    }
}
