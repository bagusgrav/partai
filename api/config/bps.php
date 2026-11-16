<?php

return [
    'api_key' => env('BPS_API_KEY'),
    'base_url' => env('BPS_API_BASE_URL', 'https://webapi.bps.go.id/v1/api'),
    'domain' => env('BPS_API_DOMAIN', '0000'),
    'mock_year' => env('BPS_MOCK_YEAR', 2024),
    'indicators' => [
        'kemiskinan' => [
            'variable_id' => env('BPS_POVERTY_VARIABLE_ID'),
            'period_id' => env('BPS_POVERTY_PERIOD_ID'),
            'year' => env('BPS_POVERTY_YEAR', 2024),
        ],
        'stunting' => [
            'variable_id' => env('BPS_STUNTING_VARIABLE_ID'),
            'period_id' => env('BPS_STUNTING_PERIOD_ID'),
            'year' => env('BPS_STUNTING_YEAR', 2024),
        ],
    ],
];
