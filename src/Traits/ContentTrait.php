<?php

namespace Encore\Admin\Traits;

trait ContentTrait {

    protected static $contentTitle;
    protected static $contentTitlePlural;
    protected static $contentSlug;
    protected static $contentFrontedSlug;
    protected static $initedContentVariables = false;

    protected static function initStatic() {}

    protected static function initContentVariables() {
        static::initStatic();
        static::$initedContentVariables = true;
    }

    public static function getContentTitle(){
        if (!static::$initedContentVariables) static::initContentVariables();
        return static::$contentTitle;
    }

    public static function getContentTitlePlural(){
        if (!static::$initedContentVariables) static::initContentVariables();
        return static::$contentTitlePlural;
    }

    public static function getContentSlug(){
        if (!static::$initedContentVariables) static::initContentVariables();
        return static::$contentSlug;
    }

    public static function getContentFrontendSlug(){
        if (!static::$initedContentVariables) static::initContentVariables();
        return static::$contentFrontedSlug;
    }

    public static function label($field){
        if (!static::$initedContentVariables) static::initContentVariables();
        return \Lang::has("{__CLASS__}.{$field}") ? __("{__CLASS__}.{$field}") : __("{$field}");
    }

}