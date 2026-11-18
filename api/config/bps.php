<?php

return [
    'api_key' => env('BPS_API_KEY'),
    'api_url' => env('BPS_API_URL', env('BPS_API_BASE_URL', 'https://webapi.bps.go.id/v1/api')),
    'base_url' => env('BPS_API_URL', env('BPS_API_BASE_URL', 'https://webapi.bps.go.id/v1/api')),
    'ssl_verify' => env('BPS_SSL_VERIFY', true),
    'ikk_data_url' => env('BPS_IKK_DATA_URL'),
    'domain' => env('BPS_API_DOMAIN', '0000'),
    'mock_year' => env('BPS_MOCK_YEAR', 2024),
    'economic_history_start_year' => env('BPS_ECONOMIC_HISTORY_START_YEAR', 2015),
    'regional_history_start_year' => env('BPS_REGIONAL_HISTORY_START_YEAR', 2021),
    'inflation_variable_id' => env('BPS_INFLATION_VARIABLE_ID', 1),
    'regional_indicators' => [
        'inflasi' => env('BPS_REGIONAL_INFLATION_VARIABLE_ID', 104),
        'kemiskinan' => env('BPS_REGIONAL_POVERTY_VARIABLE_ID', 192),
        'tpt' => env('BPS_REGIONAL_TPT_VARIABLE_ID', 543),
        'gini' => env('BPS_REGIONAL_GINI_VARIABLE_ID', 2103),
    ],
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
