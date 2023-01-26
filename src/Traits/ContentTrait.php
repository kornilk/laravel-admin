<?php

namespace Encore\Admin\Traits;

trait ContentTrait {

    protected static $contentTitle;
    protected static $contentTitlePlural;
    protected static $contentSlug;
    protected static $contentPermissionName = null;
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

    public static function getContentPermissionName(){
        if (!static::$initedContentVariables) static::initContentVariables();
        return static::$contentPermissionName ? static::$contentPermissionName : static::getContentSlug() ;
    }

    public static function getContentAdminRoute($method = 'show', $parameters = []){
        if (!static::$initedContentVariables) static::initContentVariables();
        $slug = str_replace("/", ".", static::getContentSlug());
        return route("admin.{$slug}.{$method}", $parameters);
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
        $class = \Str::afterLast(static::class, '\\');
        if (!static::$initedContentVariables) static::initContentVariables();
        $field = \Str::of($field)->replaceLast('_id', '');
        $fieldName = ucfirst(strtolower(\Str::headline($field)));
        $ucField = ucfirst($field);
        return \Lang::has("{$class}.{$field}") ? __("{$class}.{$field}") : (
            \Lang::has("{$class}.{$fieldName}") ? __("{$class}.{$fieldName}") : (
            \Lang::has("{$field}") ? __("{$field}") : (\Lang::has("{$ucField}") ? __("{$ucField}") : $fieldName)
        ));
    }

    public function getContentReadableIdentifierAttribute(){
        return $this->{static::getContentBaseColumn()};
    }

    public static function getReadableValue($field, $value){

        if (!is_string($value)) $value = @json_encode($value);
        if (!is_string($value)) $value = '';

        $field = ucfirst($field);
        if (method_exists(static::class, "readable{$field}Value")) return static::{"readable{$field}Value"}($value);
        $value = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value);
        
        return mb_strimwidth(strip_tags($value), 0, 1000, "...");
    }

    public static function readableImage_idValue($value){

        $image = \Encore\Admin\Models\Image::where('id', $value)->first();

        if (!$image) return '-';

        return $image->readablePathValue($image->path);
    }

    public static function readableActiveValue($value){
        return $value === 1 ? __('Yes') : __('No');
    }


}