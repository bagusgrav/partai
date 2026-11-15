<?php

return [
    'paths' => [
        'api/user',
        'api/review',
        'api/answer',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],

    'allowed_origins' => [
        'https://akuhadir.my.id',
        'https://www.akuhadir.my.id',
        'http://localhost:3000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'Origin',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
