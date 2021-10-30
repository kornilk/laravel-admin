<?php

namespace Encore\Admin\Grid\Displayers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Storage;

class Image extends AbstractDisplayer
{
    public function display($thumbnail = '', $popup = '', $width = 200, $height = 200)
    {
        if ($this->value instanceof Arrayable) {
            $this->value = $this->value->toArray();
        }

        return collect((array) $this->value)->filter()->map(function ($path) use ($thumbnail, $popup, $width, $height) {
            if (empty($path)) {
                return '';
            }

            if (empty($thumbnail)) $thumbnail = config('image.defaultThumbName');

            $image = pathinfo($path);
            $thumb = $image['dirname'] . '/' . $image['filename'] . '-' . $thumbnail . '.' . $image['extension'];

            $popup = $image['dirname'] . '/' . $image['filename'] . (!empty($popup) ? "-{$popup}" : '') . '.' . $image['extension'];
            return '<a href="' . Storage::disk(config('admin.upload.disk'))->url($popup) . '" class="grid-popup-link card-img-top">
            <img src="' . Storage::disk(config('admin.upload.disk'))->url($thumb) . '" style="max-width:'.$width.'px;max-height:'.$height.'px" class="img img-thumbnail"></a>';
        })->implode('&nbsp;');
    }
}
