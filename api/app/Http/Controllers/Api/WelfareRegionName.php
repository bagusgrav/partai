<?php

namespace App\Http\Controllers\Api;

use App\Models\WelfareData;

class WelfareRegionName
{
    public static function provinceForCluster(string $clusterId): ?string
    {
        return WelfareData::query()
            ->where('region_code', $clusterId)
            ->value('region_name');
    }
}
