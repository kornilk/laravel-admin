<?php

namespace Encore\Admin\Models;

use Illuminate\Database\Eloquent\Builder;

class ImageMedium extends Image
{
    protected $table = 'images'; 

    public static function boot()
    {
        parent::boot();

        self::addGlobalScope('width', function (Builder $builder) {
            $builder->where('width', '>=', config('image.rules.medium.minWidth'));
        });

        self::addGlobalScope('height', function (Builder $builder) {
            $builder->where('height', '>=', config('image.rules.medium.minHeight'));
        });
    }
}
