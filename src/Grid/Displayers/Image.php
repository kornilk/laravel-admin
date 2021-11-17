<?php

namespace Encore\Admin\Grid\Displayers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Storage;

class Image extends AbstractDisplayer
{
    public function display($thumbnail = null, $popup = '', $width = 200, $height = 200)
    {
        
        if (empty($thumbnail)) {

            if (property_exists($this->row, 'image_class') && method_exists($this->row->image_class, 'getDefaultThumbName')){
                $thumbnail = $this->row->image_class::getDefaultThumbName();
            } else {
                $relation = $this->column->getRelation();
                $thumbnail = ($relation && $this->row->{$relation}) ? $this->row->{$relation}->image_class::getDefaultThumbName() : 'thumb';
            }
        }

        if ($this->value instanceof Arrayable) {
            $this->value = $this->value->toArray();
        }

        return collect((array) $this->value)->filter()->map(function ($path) use ($thumbnail, $popup, $width, $height) {
            if (empty($path)) {
                return '';
            }

            $image = pathinfo($path);
            $thumb = $image['dirname'] . '/' . $image['filename'] . '-' . $thumbnail . '.' . $image['extension'];

            if ($popup === false){
                return '<img src="' . Storage::disk(config('admin.upload.disk'))->url($thumb) . '" style="max-width:'.$width.'px;max-height:'.$height.'px" class="img img-thumbnail">';
            } else {
                $popup = $image['dirname'] . '/' . $image['filename'] . (!empty($popup) ? "-{$popup}" : '') . '.' . $image['extension'];
                return '<a href="' . Storage::disk(config('admin.upload.disk'))->url($popup) . '" class="grid-popup-link card-img-top">
                <img src="' . Storage::disk(config('admin.upload.disk'))->url($thumb) . '" style="max-width:'.$width.'px;max-height:'.$height.'px" class="img img-thumbnail"></a>';
            }
            
        })->implode('&nbsp;');
    }
}
