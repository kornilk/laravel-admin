<?php

namespace Encore\Admin\Observers;

use Illuminate\Support\Facades\Cache;


class CacheObserver
{

    public function created()
    {
        Cache::flush();
    }

    public function updated()
    {
        Cache::flush();
    }

    public function deleted()
    {
        Cache::flush();
    }

}
