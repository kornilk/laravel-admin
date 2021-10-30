<?php

return [

    'defaultThumbName' => 'thumb',

    'watermark' => null, //public_path() .'/admin-assets/images/watermark.png',

    'sizes' => [
        'default' => [
            'thumbnails' => [
                'thumb' => [320, null],
                'thumb-r' => [320, 320, 'fit'],
                'medium' => [750, null],
                'medium-r' => [750, 750, 'fit'],
            ],
            'max' => 1920,
        ]
    ],

    'keepOriginal' => true,

    'rules' => [
        'medium' => [
            'minWidth' => 500,
            'minHeight' => 500,
        ]
    ]

];