<?php

return [

    'enabled' => env('FIREWALL_ENABLED', true),

    'whitelist' => [env('FIREWALL_WHITELIST', '')],

    'models' => [
        'user' => '\App\Admin\Models\Administrator',
        // 'log' => '\App\Models\YourLogModel',
        // 'ip' => '\App\Models\YourIpModel',
    ],

    'responses' => [

        'block' => [
            'view' => env('FIREWALL_BLOCK_VIEW', null),
            'redirect' => env('FIREWALL_BLOCK_REDIRECT', null),
            'abort' => env('FIREWALL_BLOCK_ABORT', false),
            'code' => env('FIREWALL_BLOCK_CODE', 403),
        ],

    ],

    'notifications' => [

        'mail' => [
            'enabled' => env('FIREWALL_EMAIL_ENABLED', true),
            'name' => env('FIREWALL_EMAIL_NAME', 'Firewall'),
            'from' => env('FIREWALL_EMAIL_FROM', 'noreply@s11.digital'),
            'to' => env('FIREWALL_EMAIL_TO', 'dev@s11.digital'),
        ],

        'slack' => [
            'enabled' => env('FIREWALL_SLACK_ENABLED', false),
            'emoji' => env('FIREWALL_SLACK_EMOJI', ':fire:'),
            'from' => env('FIREWALL_SLACK_FROM', 'Laravel Firewall'),
            'to' => env('FIREWALL_SLACK_TO'), // webhook url
            'channel' => env('FIREWALL_SLACK_CHANNEL', null), // set null to use the default channel of webhook
        ],

    ],

    'all_middleware' => [
        'firewall.ip',
        //'firewall.agent',
        'firewall.bot',
        'firewall.geo',
        'firewall.lfi',
        'firewall.php',
        'firewall.referrer',
        'firewall.rfi',
        'firewall.session',
        'firewall.sqli',
        'firewall.swear',
        'firewall.xss',
        //'App\Http\Middleware\YourCustomRule',
    ],

    'middleware' => [

        'ip' => [
            'methods' => ['all'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],
        ],

        'agent' => [
            'methods' => ['all'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            // https://github.com/jenssegers/agent
            'browsers' => [
                'allow' => [], // i.e. 'Chrome', 'Firefox'
                'block' => [], // i.e. 'IE'
            ],

            'platforms' => [
                'allow' => [], // i.e. 'Ubuntu', 'Windows'
                'block' => [], // i.e. 'OS X'
            ],

            'devices' => [
                'allow' => [], // i.e. 'Desktop', 'Mobile'
                'block' => [], // i.e. 'Tablet'
            ],

            'properties' => [
                'allow' => [], // i.e. 'Gecko', 'Version/5.1.7'
                'block' => [], // i.e. 'AppleWebKit'
            ],

            'auto_block' => [
                'attempts' => 3,
                'frequency' => 60 * 60 * 24,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'bot' => [
            'methods' => ['all'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            // https://github.com/JayBizzle/Crawler-Detect/blob/master/raw/Crawlers.txt
            'crawlers' => [
                'allow' => [], // i.e. 'GoogleSites', 'GuzzleHttp'
                'block' => [], // i.e. 'Holmes'
            ],

            'auto_block' => [
                'attempts' => 3,
                'frequency' => 60 * 60 * 24,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'geo' => [
            'methods' => ['all'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            'continents' => [
                'allow' => [], // i.e. 'Africa'
                'block' => [], // i.e. 'Europe'
            ],

            'regions' => [
                'allow' => [], // i.e. 'California'
                'block' => [], // i.e. 'Nevada'
            ],

            'countries' => [
                'allow' => ['Hungary'], // i.e. 'Albania'
                'block' => [], // i.e. 'Madagascar'
            ],

            'cities' => [
                'allow' => [], // i.e. 'Istanbul'
                'block' => [], // i.e. 'London'
            ],

            // ipapi, extremeiplookup, ipstack, ipdata, ipinfo
            'service' => 'ipapi',

            'auto_block' => [
                'attempts' => 3,
                'frequency' => 60 * 60 * 24,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'lfi' => [
            'methods' => ['get', 'delete'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            'inputs' => [
                'only' => [], // i.e. 'first_name'
                'except' => [], // i.e. 'password'
            ],

            'patterns' => [
                '#\.\/#is',
            ],

            'auto_block' => [
                'attempts' => 3,
                'frequency' => 60 * 60 * 24,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'login' => [
            'enabled' => true,

            'auto_block' => [
                'attempts' => 10,
                'frequency' => 60 * 60,
                'period' => 60 * 60 * 24,
            ],
        ],

        'php' => [
            'methods' => ['get', 'post', 'delete'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            'inputs' => [
                'only' => [], // i.e. 'first_name'
                'except' => [], // i.e. 'password'
            ],

            'patterns' => [
                'bzip2://',
                'expect://',
                'glob://',
                'phar://',
                'php://',
                'ogg://',
                'rar://',
                'ssh2://',
                'zip://',
                'zlib://',
            ],

            'auto_block' => [
                'attempts' => 3,
                'frequency' => 60 * 60 * 24,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'referrer' => [
            'methods' => ['all'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            'blocked' => [],

            'auto_block' => [
                'attempts' => 3,
                'frequency' => 60 * 60 * 24,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'rfi' => [
            'methods' => ['get', 'post', 'delete'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            'inputs' => [
                'only' => [], // i.e. 'first_name'
                'except' => [], // i.e. 'password'
            ],

            'patterns' => [
                '#(http|ftp){1,1}(s){0,1}://.*#i',
            ],

            'exceptions' => [],

            'auto_block' => [
                'attempts' => 3,
                'frequency' => 60 * 60 * 24,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'session' => [
            'methods' => ['get', 'post', 'delete'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            'inputs' => [
                'only' => [], // i.e. 'first_name'
                'except' => [], // i.e. 'password'
            ],

            'patterns' => [
                '@[\|:]O:\d{1,}:"[\w_][\w\d_]{0,}":\d{1,}:{@i',
                '@[\|:]a:\d{1,}:{@i',
            ],

            'auto_block' => [
                'attempts' => 3,
                'frequency' => 60 * 60 * 24,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'sqli' => [
            'methods' => ['get', 'delete'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            'inputs' => [
                'only' => [], // i.e. 'first_name'
                'except' => [], // i.e. 'password'
            ],

            'patterns' => [
                '#[\d\W](union select|union join|union distinct)[\d\W]#is',
                '#[\d\W](union|union select|insert|from|where|concat|into|cast|truncate|select|delete|having)[\d\W]#is',
            ],

            'auto_block' => [
                'attempts' => 3,
                'frequency' => 60 * 60 * 24,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'swear' => [
            'methods' => ['post', 'put', 'patch'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            'inputs' => [
                'only' => [], // i.e. 'first_name'
                'except' => [], // i.e. 'password'
            ],

            'words' => [],

            'auto_block' => [
                'attempts' => 3,
                'frequency' => 60 * 60 * 24,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'url' => [
            'methods' => ['all'],

            'inspections' => ['wp-admin'], // i.e. 'admin'

            'auto_block' => [
                'attempts' => 1,
                'frequency' => 60 * 60,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

        'whitelist' => [
            'methods' => ['all'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],
        ],

        'xss' => [
            'methods' => ['post', 'put', 'patch'],

            'routes' => [
                'only' => [], // i.e. 'contact'
                'except' => [], // i.e. 'admin/*'
            ],

            'inputs' => [
                'only' => [], // i.e. 'first_name'
                'except' => [], // i.e. 'password'
            ],

            'patterns' => [
                // Evil starting attributes
                '#(<[^>]+[\x00-\x20\"\'\/])(form|formaction|on\w*|style|xmlns|xlink:href)[^>]*>?#iUu',

                // javascript:, livescript:, vbscript:, mocha: protocols
                '!((java|live|vb)script|mocha|feed|data):(\w)*!iUu',
                '#-moz-binding[\x00-\x20]*:#u',

                // Unneeded tags
                '#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base|img)[^>]*>?#i'
            ],

            'auto_block' => [
                'attempts' => 20,
                'frequency' => 60 * 60,
                'period' => 60 * 60 * 24 * 365 * 1000,
            ],
        ],

    ],

];
