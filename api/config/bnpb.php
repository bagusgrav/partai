<?php

return [
    'api_key' => env('BNPB_API_KEY'),
    'api_url' => env(
        'BNPB_API_URL',
        'https://gis.bnpb.go.id/server/rest/services/Jumlah_Bencana_kabkot_2025/MapServer/13/query',
    ),
    'data_year' => env('BNPB_DATA_YEAR', 2025),
];
