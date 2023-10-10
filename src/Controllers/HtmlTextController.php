<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Models\HtmlText;
use Encore\Admin\Show;

class HtmlTextController extends AdminController
{
    protected $model = HtmlText::class;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new $this->model());

        $grid->column('value', $this->model::label('value'))->stripTags()->edit();
        $grid->column('context', $this->model::label('context'));
        $grid->column('placeholder', $this->model::label('placeholder'));
       
        $grid->filter(function($filter){

            $filter->where(function ($query) {

                $decoded = htmlentities($this->input);
                $query->whereTranslation('value', 'like', "%{$decoded}%")
                    ->orWhere('context', 'like', "%{$this->input}%")
                    ->orWhere('placeholder', 'like', "%{$this->input}%");
            
            }, __('admin.Search'));

        
        });

        if (config('admin.contents.html_text.disable_delete', true)){
            $grid->actions(function ($actions) {
                $actions->disableDelete();
            });

            $grid->batchActions(function ($batch) {
                $batch->disableDelete();
            });
        }

        if (config('admin.contents.html_text.disable_create', true)){
            $grid->disableCreateButton();
        }

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show($this->model::findOrFail($id));

        $show->field('value', $this->model::label('value'))->unescape();
        $show->field('context', $this->model::label('context'));
        $show->field('placeholder', $this->model::label('placeholder'));

        $show->panel()->tools(function ($tools) {
            if (config('admin.contents.html_text.disable_delete', true)){
                $tools->disableDelete();
            }
        });
       
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function setForm($form)
    {
        $form->editor('value', $this->model::label('value'))->options(['customConfig' => '/vendor/laravel-admin/ckeditor/config_html-text.js'])->required();
       
        $form->display('context', $this->model::label('context'))->required()->rules('max:190');

        $form->display('placeholder', $this->model::label('placeholder'))->required()->rules('max:190');

        $form->footer(function ($footer) {
            if (config('admin.contents.html_text.disable_create', true)){
                $footer->disableCreatingCheck();
            }
        });

        $form->tools(function (Form\Tools $tools) {
            if (config('admin.contents.html_text.disable_delete', true)){
                $tools->disableDelete();
            }
        });
        
        return $form;
    }
}
