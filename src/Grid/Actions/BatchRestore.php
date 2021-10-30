<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchRestore extends BatchAction
{
    public $name = 'Csoportos visszaállítás';

    public function handle (Collection $collection)
    {
        $collection->each->restore();

        return $this->response()->success(__('admin.Successfully restored'))->refresh();
    }

    public function dialog ()
    {
        $this->confirm(__('admin.Are you sure you want to restore?'));
    }
}