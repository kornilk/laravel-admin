<?php

namespace Encore\Admin\Traits;

trait Imageable
{
    protected static $thumbnails = null;
    protected static $defaultThumbName = null;
    protected static $watermark = null;
    protected static $maxSize = null;
    protected static $keepOriginal = null;
    protected static $rules = null;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->setImageData($model);
            $model->image_class = get_called_class();
        });


        self::updating(function ($model) {

            if ($model->path !== $model->original['path']) {
                $model->setImageData($model);
            }
        });
    }

    public static function getThumbnails(){
        return !is_null(static::$thumbnails) ? static::$thumbnails : config('image.thumbnails');
    }

    public static function getDefaultThumbName(){
        return !is_null(static::$defaultThumbName) ? static::$defaultThumbName : config('image.defaultThumbName');
    }

    public static function getWatermark(){
        return !is_null(static::$watermark) ? static::$watermark : config('image.watermark');
    }

    public static function getMaxSize(){
        return !is_null(static::$maxSize) ? static::$maxSize : config('image.maxSize');
    }

    public static function getKeepOriginal(){
        return !is_null(static::$keepOriginal) ? static::$keepOriginal : config('image.keepOriginal');
    }

    public static function getRules(){
        return !is_null(static::$rules) ? static::$rules : config('image.rules');
    }

    private function setImageData($model)
    {
        $r_path = \Storage::disk(config('admin.upload.disk'))->path($model->path);
        list($width, $height) = getimagesize($r_path);
        $path_parts = pathinfo($r_path);

        $model->width = $width;
        $model->height = $height;

        if (is_array($path_parts)) {
            $model->filename = $path_parts['filename'];
            $model->extension = $path_parts['extension'];
        }

        $path_parts = pathinfo($model->path);
        foreach (config('image.thumbnails') as $key => $thumb) {

            $thumb_path = \Storage::disk(config('admin.upload.disk'))->path($path_parts['dirname'] . '/' . $path_parts['filename'] . '-' . $key . '.' . $path_parts['extension']);
            list($width, $height) = getimagesize($thumb_path);

            $thumbnails[$key] = [
                'path' => $path_parts['dirname'] . '/' . $path_parts['filename'] . '-' . $key . '.' . $path_parts['extension'],
                'width' => $width,
                'height' => $height,
            ];
        }
        $model->formats = json_encode($thumbnails);
    }
}