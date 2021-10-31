<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Auth\Database\Permission;
use Illuminate\Database\Eloquent\Collection;

class BatchForceDelete extends BatchAction
{
    public function name()
    {
        return __('admin.Batch force delete');
    }

    public function handle (Collection $collection)
    {
        $collection->each->forceDelete();

        return $this->response()->success(__('admin.Permanently deleted'))->refresh();
    }

    public function dialog ()
    {
        $this->confirm(__('admin.Are you sure you want to delete permanently?'));
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