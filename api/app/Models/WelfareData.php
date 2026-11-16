<?php

namespace App\Models;

use Database\Factories\WelfareDataFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelfareData extends Model
{
    /** @use HasFactory<WelfareDataFactory> */
    use HasFactory;

    protected $fillable = [
        'region_code',
        'region_name',
        'indicator',
        'value',
        'unit',
        'year',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'year' => 'integer',
        ];
    }
}
