<?php

namespace Encore\Admin\Models;

use Encore\Admin\Traits\ContentTrait;
use Encore\Admin\Traits\Imageable;
use Storage;

class Image extends ContentModel
{
    use ContentTrait, Imageable;

    protected $recordableExcludeColumns = [
        'width',
        'height',
        'filename',
        'extension',
        'formats',
        'image_class',
    ];

    protected static function initStatic()
    {
        static::$contentTitle = 'Image';
        static::$contentTitlePlural = 'Images';
        static::$contentSlug = 'images';
    }

    public static function getContentBaseColumn(){
        return 'title';
    }

    public static function readablePathValue($value){
        $imageInfo = pathinfo($value);
        $thumbName = static::getDefaultThumbName();
        $path = Storage::disk(config('admin.upload.disk'))->url("{$imageInfo['dirname']}/{$imageInfo['filename']}-{$thumbName}.{$imageInfo['extension']}");
        return '<img class="" src="'.$path.'" />';
    }

}
