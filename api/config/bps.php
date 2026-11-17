<?php

return [
    'api_key' => env('BPS_API_KEY'),
    'api_url' => env('BPS_API_URL', env('BPS_API_BASE_URL', 'https://webapi.bps.go.id/v1/api')),
    'base_url' => env('BPS_API_URL', env('BPS_API_BASE_URL', 'https://webapi.bps.go.id/v1/api')),
    'ikk_data_url' => env('BPS_IKK_DATA_URL'),
    'domain' => env('BPS_API_DOMAIN', '0000'),
    'mock_year' => env('BPS_MOCK_YEAR', 2024),
    'economic_history_start_year' => env('BPS_ECONOMIC_HISTORY_START_YEAR', 2015),
    'inflation_variable_id' => env('BPS_INFLATION_VARIABLE_ID', 1),
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
