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

        $grid->column('value', __('text'))->stripTags()->edit();
        $grid->column('context', __('context'));
        $grid->column('placeholder', __('placeholder'));
       
        $grid->filter(function($filter){

            $filter->disableIdFilter();

            $filter->where(function ($query) {

                $query->where('value', 'like', "%{$this->input}%")
                    ->orWhere('context', 'like', "%{$this->input}%")
                    ->orWhere('placeholder', 'like', "%{$this->input}%");
            
            }, __('admin.Search'));

        
        });

        $grid->disableCreateButton();

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

        $show->field('value', __('text'))->unescape();
        $show->field('context', __('ccontext'));
        $show->field('placeholder', __('placeholder'));

        $show->panel()->tools(function ($tools) {
            $tools->disableDelete();
        });
       
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new $this->model());

        $form->ckeditor('value', __('text'))->options(['customConfig' => '/vendor/laravel-admin/ckeditor/config_html-text.js'])->attribute('id', 'htmlTextValue')->attribute('class', 'ckEditorTextarea')->rules('required');
       
        $form->text('context', __('context'))->rules('required|max:190');

        $form->text('placeholder', __('placeholder'))->rules('required|max:190');

        $form->footer(function ($footer) {
            $footer->disableCreatingCheck();
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });
        
        return $form;
    }
}
