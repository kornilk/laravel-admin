<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\RowAction;
use Encore\Admin\Auth\Database\Permission;
use Illuminate\Database\Eloquent\Model;

class ForceDelete extends RowAction
{
    public function name()
    {
        return __('admin.Force delete');
    }

    public function handle (Model $model)
    {
        $model->forceDelete();

        return $this->response()->success(__('admin.Permanently deleted'))->refresh();
    }

    public function dialog()
    {
        $this->confirm(__('admin.Are you sure you want to delete permanently?'));
    }

    protected function authorize($user, $model){

        if(is_a($model, 'Illuminate\Database\Eloquent\Collection')) {
            $model = $model[0];
        }

        $permissionName = method_exists($model, 'getContentPermissionName') ? $model::getContentPermissionName() : null;

        if (empty($permissionName)) return true;

        $permission = "{$permissionName}.destroy";

        if (Permission::isPermission($permission) && !$user->can($permission)){
            return false;
        }

        return true;
    }
}