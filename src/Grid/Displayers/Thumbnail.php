<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;
use Storage;
use Str;

class Thumbnail extends AbstractDisplayer
{
    public function display($thumbName = NULL, $popup = ''){

        if (!empty($this->value)) {

            if (empty($thumbName)) $thumbName = config('image.defaultThumbName');

            $data = pathinfo($this->value);
            $thumb = $data['dirname'] . '/' . $data['filename'] . '-' . $thumbName . '.' . $data['extension'];

            if (!empty($popup)) $popup = '-' . $popup;

            $popup = $data['dirname'] . '/' . $data['filename'] . $popup . '.' . $data['extension'];
            return '<a href="' . Storage::disk(config('admin.upload.disk'))->url($popup) . '" class="grid-popup-link card-img-top">
            <img src="' . Storage::disk(config('admin.upload.disk'))->url($thumb) . '" style="max-width:200px;max-height:100px" class="img img-thumbnail"></a>';
        }
        return $this->value;
    }
}
