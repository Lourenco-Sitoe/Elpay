<?php

return [
    // mock | mpesa | emola — também legível via settings (payment_driver)
    'default' => env('PAYMENT_DRIVER', 'mock'),

    'drivers' => [
        'mock' => [],
        'mpesa' => [
            'api_key' => env('MPESA_API_KEY'),
            'public_key' => env('MPESA_PUBLIC_KEY'),
            'origin' => env('MPESA_ORIGIN'),
            'endpoint' => env('MPESA_ENDPOINT', 'https://api.sandbox.vm.co.mz'),
        ],
        'emola' => [
            'api_key' => env('EMOLA_API_KEY'),
            'endpoint' => env('EMOLA_ENDPOINT', 'https://api.sandbox.emola.co.mz'),
        ],
    ],
];
