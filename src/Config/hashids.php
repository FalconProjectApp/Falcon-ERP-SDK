<?php

return [
    'connections' => [
        'falcon' => [
            'salt'     => env('HASHIDS_SALT', 'example'),
            'length'   => 10,
            'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
        ],
    ],
];
