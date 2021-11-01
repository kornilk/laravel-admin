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
        return !empty(static::$contentFrontedSlug) ? static::$contentFrontedSlug : static::$contentSlug;
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public static function getContentBaseColumn(){
        return \Schema::getColumnListing(static::getTableName())[1];
    }

    public static function label($field){
        $class = \Str::afterLast(__CLASS__, '\\');
        if (!static::$initedContentVariables) static::initContentVariables();
        $field = \Str::of($field)->replaceLast('_id', '');
        $fieldName = ucfirst(strtolower(\Str::headline($field)));
        return \Lang::has("{$class}.{$field}") ? __("{$class}.{$field}") : (
            \Lang::has("{$field}") ? __("{$field}") : $fieldName
        );
    }

    public function getContentReadableIdentifierAttribute(){
        return $this->{static::getContentBaseColumn()};
    }

}