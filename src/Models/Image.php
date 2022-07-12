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
        'picture',
        'storage_path',
    ];
    protected $appends = [
        'picture',
        'storage_path',
    ];

    protected static function initStatic()
    {
        static::$contentTitle = 'Image';
        static::$contentTitlePlural = 'Images';
        static::$contentSlug = 'images';
    }

    public static function getContentBaseColumn()
    {
        return 'title';
    }

    public static function readablePathValue($value)
    {
        $imageInfo = pathinfo($value);
        $thumbName = static::getDefaultThumbName();
        $path = str_replace(\URL::to('/'), '', Storage::disk(config('admin.upload.disk'))->url("{$imageInfo['dirname']}/{$imageInfo['filename']}-{$thumbName}.{$imageInfo['extension']}"));
        return '<img class="" src="' . $path . '" />';
    }

    public function getStoragePathAttribute()
    {
        return str_replace(\URL::to('/'), '', Storage::disk(config('admin.upload.disk'))->url($this->path));
    }

    public function getPictureAttribute()
    {
        $picture = config('image.ckeditorPicture');

        if (!$picture) {
            $thumbs = [];
            $picture = [];

            $configThumbs = config('image.thumbnails');

            foreach ($configThumbs as $key => $value) {
                if (!is_null($value[1])) continue;
                $thumbs[$value[0]] = $key;
            }
            ksort($thumbs);
            $minSize = array_key_first($thumbs);
            $picture['default'] = reset($thumbs);

            unset($thumbs[$minSize]);

            $sources = [];

            foreach ($thumbs as $size => $name) {
                $sources[$minSize + 1] = $name;
                $minSize = $size;
            }

            $picture['sources'] = $sources;

            $maxSize = $configThumbs[$picture['sources'][array_key_last($picture['sources'])]][0];

            if ($maxSize < config('image.maxSize')) {
                $picture['sources'][$maxSize + 1] = '';
            }
        }

        $p = [
            'default' => $this->getPictureData($picture['default'], $this),
            'sources' => [],
        ];

        foreach ($picture['sources'] as $key => $value) {
            $p['sources'][$key] = $this->getPictureData($value, $this);
        }

        return $p;
    }

    public function getPictureData($thumb = '', $model)
    {

        if (is_null($model)) $model = $this;


        if (empty($thumb)) {

            return [
                'path' => str_replace(\URL::to('/'), '', \Storage::disk(config('admin.upload.disk'))->url($model->path)),
                'width' => $model->width,
                'height' => $model->height,
            ];
        } else {
            $formats = json_decode($model->formats);
            $return = $formats->{$thumb};
            $return->path = str_replace(\URL::to('/'), '', \Storage::disk(config('admin.upload.disk'))->url($return->path));
            return $return;
        }
    }
}