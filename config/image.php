<?php

return [

    'defaultThumbName' => 'thumb',

    'useFilenameAsImageTitle' => true,

    'watermark' => null, //public_path() .'/admin-assets/images/watermark.png',

    'thumbnails' => [ //[width, height, method, position, upscale (retain maximal original image size)(default false)]
        'thumb' => [320, null, null, null, true],
        'thumb-c' => [320, 320, 'fit'],
        'thumb-r' => [320, 180, 'fit'],
        'medium' => [750, null, null, null, true],
        'medium-c' => [750, 750, 'fit'],
        'medium-r' => [750, 422, 'fit'],
    ],

    'useFilenameAsImageTitle' => false,

    'ckeditorPicture' => null, //['default' => thumb, 'sources' => [1000 => 'medium', 1900 => null]]

    'maxSize' => 1920,

    'keepOriginal' => false,
    'deleteFiles' => false,

    'rules' => [
        'minWidth' => 320,
        'minHeight' => 320,
    ]

];
