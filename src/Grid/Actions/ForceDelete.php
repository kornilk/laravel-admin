<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class ForceDelete extends RowAction
{
    public $name = 'Végleges törlés';

    public function handle (Model $model)
    {
        $model->forceDelete();

        return $this->response()->success(__('content.Permanently deleted'))->refresh();
    }

    public function dialog()
    {
        $this->confirm(__('content.Are you sure you want to delete permanently?'));
    }
}