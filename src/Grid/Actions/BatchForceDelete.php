<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchForceDelete extends BatchAction
{
    public $name = 'Csoportos végleges törlés';

    public function handle (Collection $collection)
    {
        $collection->each->forceDelete();

        return $this->response()->success(__('admin.Permanently deleted'))->refresh();
    }

    public function dialog ()
    {
        $this->confirm(__('admin.Are you sure you want to delete permanently?'));
    }
}