<?php

return [

    'domain' => env('PROJECT_DOMAIN', 'localhost'),
    'subdomain_api' => env('SUBDOMAIN_API', 'api'),
    'subdomain_admin' => env('SUBDOMAIN_ADMIN', 'admin'),

    'cache_expire_base' => env('CACHE_EXPIRE_BASE', 10),
    'cache_expire_translation' => env('CACHE_EXPIRE_TRANSLATION', 10),

    'project_name' => env('PROJECT_NAME', ''),
    
];
