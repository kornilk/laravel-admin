<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\RowAction;
use Encore\Admin\Auth\Database\Permission;
use Illuminate\Database\Eloquent\Model;

class Restore extends RowAction
{
    public function name()
    {
        return __('admin.Restore');
    }

    public function handle (Model $model)
    {
        $model->restore();

        return $this->response()->success(__('admin.Successfully restored'))->refresh();
    }

    public function dialog()
    {
        $this->confirm(__('admin.Are you sure you want to restore?'));
    }

    protected function authorize($user, $model){

        if(is_a($model, 'Illuminate\Database\Eloquent\Collection')) {
            $model = $model[0];
        }

        $slug = method_exists($model, 'getContentSlug') ? $this->slug = $model::getContentSlug() : null;

        if (empty($slug)) return true;

        $permission = "{$slug}.destroy";

        if (Permission::isPermission($permission) && !$user->can($permission)){
            return false;
        }

        return true;
    }
}