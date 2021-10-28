<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Restore extends RowAction
{
    public $name = 'Visszaállítás';

    public function handle (Model $model)
    {
        $model->restore();

        return $this->response()->success(__('content.Successfully restored'))->refresh();
    }

    public function dialog()
    {
        $this->confirm(__('content.Are you sure you want to restore?'));
    }
}