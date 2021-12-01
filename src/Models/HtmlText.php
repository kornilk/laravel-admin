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
        static::$contentTitle = 'Block';
        static::$contentTitlePlural = 'Blocks';
        static::$contentSlug = 'html-texts';
    }
}
