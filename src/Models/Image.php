<?php

namespace Encore\Admin\Models;

use Encore\Admin\Traits\ContentTrait;
use Storage;

class Image extends ContentModel
{
    use ContentTrait;

    protected $recordableExcludeColumns = [
        'width',
        'height',
        'filename',
        'extension',
        'formats',
    ];

    protected static function initStatic()
    {
        static::$contentTitle = __('Image');
        static::$contentTitlePlural = __('Images');
        static::$contentSlug = 'images';
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->setImageData($model);
        });


        self::updating(function ($model) {

            if ($model->path !== $model->original['path']) {
                $model->setImageData($model);
            }
        });
    }

    public static function getContentBaseColumn(){
        return 'title';
    }

    public static function readablePathValue($value){
        $imageInfo = pathinfo($value);
        $thumbName = config('image.defaultThumbName');
        $path = Storage::disk(config('admin.upload.disk'))->url("{$imageInfo['dirname']}/{$imageInfo['filename']}-{$thumbName}.{$imageInfo['extension']}");
        return '<img class="" src="'.$path.'" />';
    }

    private function setImageData($model)
    {

        $r_path = Storage::disk(config('admin.upload.disk'))->path($model->path);
        list($width, $height) = getimagesize($r_path);
        $path_parts = pathinfo($r_path);

        $model->width = $width;
        $model->height = $height;

        if (is_array($path_parts)) {
            $model->filename = $path_parts['filename'];
            $model->extension = $path_parts['extension'];
        }

        $path_parts = pathinfo($model->path);
        foreach (config('image.sizes.default.thumbnails') as $key => $thumb) {
            $thumbnails[$key] = $path_parts['dirname'] . '/' . $path_parts['filename'] . '-' . $key . '.' . $path_parts['extension'];
        }
        $model->formats = json_encode($thumbnails);
    }
}
