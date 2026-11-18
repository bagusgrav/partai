<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicAgencyScorecard extends Model
{
    use HasUuids;

    protected $fillable = [
        'agency_code',
        'agency_name',
        'agency_category',
        'province_name',
        'region_code',
        'period_year',
        'rb_index_score',
        'sakip_grade',
        'merit_system_score',
        'ombudsman_compliance_score',
        'resolved_complaints_count',
        'open_complaints_count',
        'bureaucracy_integrity_score',
        'performance_tier',
        'source_citation',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'rb_index_score' => 'float',
            'merit_system_score' => 'float',
            'ombudsman_compliance_score' => 'float',
            'resolved_complaints_count' => 'integer',
            'open_complaints_count' => 'integer',
            'bureaucracy_integrity_score' => 'float',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(BureaucracyReport::class, 'agency_id');
    }
}
