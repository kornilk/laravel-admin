<?php

namespace Encore\Admin\Traits;

trait HasUuid
{
    public static function bootHasUuid()
    {
        static::creating(function ($model) {
            $model->uuid = self::generateUuid();
        });
    }

    protected static function generateUuid(): string
    {
        do {
            $uuid = \Str::uuid();
            $query = self::where('uuid', $uuid);
        } while($query->count() > 0);

        return $uuid;
    }
}
