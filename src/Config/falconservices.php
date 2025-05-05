<?php

declare(strict_types = 1);

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
    'big_data' => [
        'production' => [
            'url_api' => env('FALCON_BIGDATA_URL', 'https://dmy48yjy7i.execute-api.us-east-1.amazonaws.com'),
        ],
        'local' => [
            'url_api' => env('FALCON_BIGDATA_URL', 'http://localhost:8007'),
        ],
    ],
    'finance' => [
        'production' => [
            'url_api' => env('FALCON_FINANCE_URL', 'https://5g2i2wdfx5.execute-api.us-east-1.amazonaws.com'),
        ],
        'local' => [
            'url_api' => env('FALCON_FINANCE_URL', 'http://localhost:8003'),
        ],
    ],
    'stock' => [
        'production' => [
            'url_api' => env('FALCON_STOCK_URL', 'https://4e84tfx5bb.execute-api.us-east-1.amazonaws.com'),
        ],
        'local' => [
            'url_api' => env('FALCON_STOCK_URL', 'http://localhost:8004'),
        ],
    ],
    'fiscal' => [
        'production' => [
            'url_api' => env('FALCON_FISCAL_URL', 'https://edupw3uheh.execute-api.us-east-1.amazonaws.com'),
        ],
        'local' => [
            'url_api' => env('FALCON_FISCAL_URL', 'http://localhost:8002'),
        ],
    ],
    'shop' => [
        'production' => [
            'url_api' => env('FALCON_SHOP_URL', 'https://n0isrx5qib.execute-api.us-east-1.amazonaws.com'),
        ],
        'local' => [
            'url_api' => env('FALCON_SHOP_URL', 'http://falcon-nginx:90'),
        ],
    ],
];
