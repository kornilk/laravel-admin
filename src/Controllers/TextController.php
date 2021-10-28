<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Models\Text;
use Encore\Admin\Show;

class TextController extends AdminController
{
    protected $model = Text::class;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new $this->model());

        $grid->column('value', __('content.Text'))->stripTags()->editLink();
        $grid->column('context', __('content.Context'));
        $grid->column('placeholder', __('content.Placeholder'));
       
        $grid->filter(function($filter){

            $filter->disableIdFilter();

            $filter->where(function ($query) {

                $query->where('value', 'like', "%{$this->input}%")
                    ->orWhere('context', 'like', "%{$this->input}%")
                    ->orWhere('placeholder', 'like', "%{$this->input}%");
            
            }, __('content.Text'));

        
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

        $show->field('value', __('content.Text'));
        $show->field('context', __('content.Context'));
        $show->field('placeholder', __('content.Placeholder'));

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

        $form->textarea('value', __('content.Text'))->rules('required');
       
        $form->text('context', __('content.Context'))->rules('required|max:190');

        $form->text('placeholder', __('content.Placeholder'))->rules('required|max:190');

        $form->footer(function ($footer) {

            $footer->disableCreatingCheck();
        
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });
        
        
        return $form;
    }
}
