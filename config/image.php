<?php

return [

    'defaultThumbName' => 'thumb',

    'watermark' => null, //public_path() .'/admin-assets/images/watermark.png',

    'thumbnails' => [
        'thumb' => [320, null],
        'thumb-r' => [320, 320, 'fit'],
        'medium' => [750, null],
        'medium-r' => [750, 750, 'fit'],
    ],

    'maxSize' => 1920,

    'keepOriginal' => false,

    'rules' => [
        'minWidth' => 320,
        'minHeight' => 320,
    ]

];
