<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
    'auth' => [
        'production' => [
            'url_api' => env('FALCON_AUTH_URL', 'https://nve3lgblkk.execute-api.us-east-1.amazonaws.com'),
        ],
        'beta' => [
            'url_api' => env('FALCON_AUTH_URL', 'http://192.168.100.144:8000/auth2'),
        ],
        'local' => [
            'url_api' => env('FALCON_AUTH_URL', 'http://localhost:8000'),
        ],
    ],
    'big_data' => [
        'production' => [
            'url_api' => env('FALCON_BIGDATA_URL', 'https://dmy48yjy7i.execute-api.us-east-1.amazonaws.com'),
        ],
        'beta' => [
            'url_api' => env('FALCON_BIGDATA_URL', 'http://192.168.100.144:8000/data-hub'),
        ],
        'local' => [
            'url_api' => env('FALCON_BIGDATA_URL', 'http://localhost:8007'),
        ],
    ],
    'finance' => [
        'production' => [
            'url_api' => env('FALCON_FINANCE_URL', 'https://5g2i2wdfx5.execute-api.us-east-1.amazonaws.com'),
        ],
        'beta' => [
            'url_api' => env('FALCON_FINANCE_URL', 'http://192.168.100.144:8000/finance'),
        ],
        'local' => [
            'url_api' => env('FALCON_FINANCE_URL', 'http://localhost:8003'),
        ],
    ],
    'stock' => [
        'production' => [
            'url_api' => env('FALCON_STOCK_URL', 'https://4e84tfx5bb.execute-api.us-east-1.amazonaws.com'),
        ],
        'beta' => [
            'url_api' => env('FALCON_STOCK_URL', 'http://192.168.100.144:8000/stock'),
        ],
        'local' => [
            'url_api' => env('FALCON_STOCK_URL', 'http://localhost:8004'),
        ],
    ],
    'fiscal' => [
        'production' => [
            'url_api' => env('FALCON_FISCAL_URL', 'https://edupw3uheh.execute-api.us-east-1.amazonaws.com'),
        ],
        'beta' => [
            'url_api' => env('FALCON_FISCAL_URL', 'http://192.168.100.144:8000/fiscal'),
        ],
        'local' => [
            'url_api' => env('FALCON_FISCAL_URL', 'http://localhost:8002'),
        ],
    ],
    'shop' => [
        'production' => [
            'url_api' => env('FALCON_SHOP_URL', 'https://9f7hkz4y84.execute-api.us-east-1.amazonaws.com'),
        ],
        'beta' => [
            'url_api' => env('FALCON_SHOP_URL', 'http://192.168.100.144:8000/shop'),
        ],
        'local' => [
            'url_api' => env('FALCON_SHOP_URL', 'http://localhost:8008'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'timeout' => 30,
];
