<?php

namespace Encore\Admin\Models;

use Encore\Admin\Traits\ContentTrait;
use Encore\Admin\Traits\Translatable;

class HtmlText extends ContentModel
{
    use ContentTrait, Translatable;

    protected $translatable = [
        'value'
    ];

    protected static function initStatic()
    {
        static::$contentTitle = __('content.Html Text');
        static::$contentTitlePlural = __('content.Html Texts');
        static::$contentSlug = 'html-texts';
    }
}
