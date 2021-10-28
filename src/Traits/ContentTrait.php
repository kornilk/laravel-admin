<?php

namespace Encore\Admin\Traits;

trait ContentTrait {

    protected static $contentTitle;
    protected static $contentTitlePlural;
    protected static $contentSlug;
    protected static $contentFrontedSlug;
    protected static $languagePrefix;
    protected static $initedContentVariables = false;

    protected static function initStatic() {}

    protected static function initContentVariables() {
        static::initStatic();
        if (empty(static::$languagePrefix)) {
            $class = explode('\\', get_called_class());
            static::$languagePrefix = end($class);
            static::$languagePrefix = strtolower(static::$languagePrefix);
        }
        
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
        $prefix = static::$languagePrefix;
        return \Lang::has("content.{$prefix}.{$field}") ? __("content.{$prefix}.{$field}") : ((\Lang::has("content.{$field}")) ? __("content.{$field}") : __("admin.{$field}"));
    }

}