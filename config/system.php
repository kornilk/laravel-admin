<?php

return [

    'domain' => env('PROJECT_DOMAIN', 'localhost'),
    'subdomain' => [
        'api' => env('SUBDOMAIN_API', 'api'),
        'admin' => env('SUBDOMAIN_API', 'admin'),
    ],

    'cache' => [
        'expire' => [
            'base' => env('CACHE_EXPIRE_BASE', 10),
            'translation' => env('CACHE_EXPIRE_TRANSLATION', 10),
        ]
    ],
        
];
