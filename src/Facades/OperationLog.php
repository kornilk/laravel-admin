<?php

namespace Encore\Admin\Facades;

use Illuminate\Support\Facades\Facade;


class OperationLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Encore\Admin\OperationLog::class;
    }
}