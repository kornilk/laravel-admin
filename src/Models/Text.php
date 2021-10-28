<?php

namespace Encore\Admin\Models;

use Encore\Admin\Traits\ContentTrait;
use Encore\Admin\Traits\Translatable;

class Text extends ContentModel
{
    use ContentTrait, Translatable;

    protected $translatable = [
        'value'
    ];

    protected static function initStatic()
    {
        static::$contentTitle = __('content.Text');
        static::$contentTitlePlural = __('content.Texts');
        static::$contentSlug = 'texts';
    }
}
