<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class PermissionController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.permissions');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $permissionModel = config('admin.database.permissions_model');

        $grid = new Grid(new $permissionModel());

        $grid->column('slug', trans('admin.slug'));
        $grid->column('name', trans('admin.name'));

        $grid->column('http_path', trans('admin.route'))->display(function ($path) {
            return collect(explode("\n", $path))->map(function ($path) {
                $method = $this->http_method ?: ['ANY'];

                if (Str::contains($path, ':')) {
                    list($method, $path) = explode(':', $path);
                    $method = explode(',', $method);
                }

                $method = collect($method)->map(function ($name) {
                    return strtoupper($name);
                })->map(function ($name) {
                    return "<span class='label label-primary'>{$name}</span>";
                })->implode('&nbsp;');

                if (!empty(config('admin.route.prefix'))) {
                    $path = '/'.trim(config('admin.route.prefix'), '/').$path;
                }

                return "<div style='margin-bottom: 5px;'>$method<code>$path</code></div>";
            })->implode('');
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        $grid->filter(function($filter){

            $filter->where(function ($query) {

                $query->where('slug', 'like', "%{$this->input}%")
                    ->orWhere('name', 'like', "%{$this->input}%")
                    ->orWhere('http_path', 'like', "%{$this->input}%");
            
            }, __('admin.Search'));

        
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $permissionModel = config('admin.database.permissions_model');

        $show = new Show($permissionModel::findOrFail($id));

        $show->field('slug', trans('admin.slug'));
        $show->field('name', trans('admin.name'));

        $show->field('http_path', trans('admin.route'))->unescape()->as(function ($path) {
            return collect(explode("\r\n", $path))->map(function ($path) {
                $method = $this->http_method ?: ['ANY'];

                if (Str::contains($path, ':')) {
                    list($method, $path) = explode(':', $path);
                    $method = explode(',', $method);
                }

                $method = collect($method)->map(function ($name) {
                    return strtoupper($name);
                })->map(function ($name) {
                    return "<span class='label label-primary'>{$name}</span>";
                })->implode('&nbsp;');

                if (!empty(config('admin.route.prefix'))) {
                    $path = '/'.trim(config('admin.route.prefix'), '/').$path;
                }

                return "<div style='margin-bottom: 5px;'>$method<code>$path</code></div>";
            })->implode('');
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $permissionModel = config('admin.database.permissions_model');

        $form = new Form(new $permissionModel());

        $form->text('slug', trans('admin.slug'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');

        $form->multipleSelect('http_method', trans('admin.http.method'))
            ->options($this->getHttpMethodsOptions())
            ->help(trans('admin.all_methods_if_empty'));
        $form->textarea('http_path', trans('admin.http.path'));

        return $form;
    }

    /**
     * Get options of HTTP methods select field.
     *
     * @return array
     */
    protected function getHttpMethodsOptions()
    {
        $model = config('admin.database.permissions_model');

        return array_combine($model::$httpMethods, $model::$httpMethods);
    }
}
