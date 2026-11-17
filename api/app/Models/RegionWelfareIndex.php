<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RegionWelfareIndex extends Model
{
    use HasUuids;

    protected $fillable = [
        'region_code',
        'region_name',
        'province_name',
        'period_year',
        'poverty_rate',
        'gini_ratio',
        'open_unemployment_rate',
        'stunting_rate',
        'disaster_events_count',
        'affected_population',
        'disaster_risk_score',
        'ikk_score',
        'vulnerability_tier',
        'bps_source_citation',
        'bnpb_source_citation',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'poverty_rate' => 'float',
            'gini_ratio' => 'float',
            'open_unemployment_rate' => 'float',
            'stunting_rate' => 'float',
            'disaster_events_count' => 'integer',
            'affected_population' => 'integer',
            'disaster_risk_score' => 'float',
            'ikk_score' => 'float',
            'last_synced_at' => 'datetime',
        ];
    }
}
