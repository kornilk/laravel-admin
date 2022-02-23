<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Auth\Database\Permission;
use Illuminate\Database\Eloquent\Collection;

class BatchRestore extends BatchAction
{
    public function name()
    {
        return __('admin.Batch restore');
    }

    public function handle (Collection $collection)
    {
        $collection->each->restore();

        return $this->response()->success(__('admin.Successfully restored'))->refresh();
    }

    public function dialog ()
    {
        $this->confirm(__('admin.Are you sure you want to restore?'));
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