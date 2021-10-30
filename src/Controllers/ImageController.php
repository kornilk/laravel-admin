<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Models\Image;
use Encore\Admin\Show;
use Encore\ModalForm\Form\ModalButton;
use Encore\ModalForm\Form\ModalForm;
use Request;

class ImageController extends AdminController
{
    protected $model = Image::class;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new $this->model());

        $grid->column('path', __('content.Image'))->image();

        $grid->column('title', __('content.Title'))->display(function($text) {
            return \Str::limit($text, 150, '...');
        });
        $grid->column('source', __('content.Source'));
        

        /*$grid->tools(function ($tools) {
            $tools->append(new GridView());
        });*/

        $grid->filter(function ($filter) {

            $filter->disableIdFilter();

            $filter->column(1 / 2, function ($filter) {
                $filter->like('title', __('content.Title'));
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->like('source', __('content.Source'));
            });
        });

        //if (Request::get('view') !== 'table') {
            $grid->setView('admin::grid.card');
        //}

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

        $show->field('path', __('content.Image'))->image();

        $show->field('title', __('content.Title'));
        $show->field('source', __('content.Source'));

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

        $this->setForm($form);

        return $form;
    }

    private function setForm(&$form){

        $rules = request('rules');
        $help = request('help');

        $form->column(4, function($form) use ($rules, $help){
            $image =  $form->image('path', __('content.Image'))->rules($rules)->required();

            if (!empty($rules)) {
                $image->rules($rules); 
            }

            if (!empty($help)) {
                $image->help($help); 
            }

        });

        $form->column(8, function($form){
            $form->text('title', __('content.Title'))->rules('max:700');
            $form->text('source', __('content.Source'))->rules('max:150');

            if (config('image.watermark')) {

                $form->switch('watermark', __('admin.Watermark'))->states($this->getYesNoSwitch());
                $form->ignore('watermark');

            }
        });

        return $form;
    }

    public function ModalFormSore(){
        return $this->ModalForm()->store();
    }

    public function ModalForm(){
        return new ModalForm(new $this->model(), function (ModalForm $form){

            $this->setForm($form);
            
            $routeAttributes = [];

            $rules = request('rules');
            $help = request('help');

            if (!empty($rules)) $routeAttributes['rules'] = $rules;
            if (!empty($help)) $routeAttributes['help'] = $help;
  
            $form->setAction(route('admin.image.modal.form.store', $routeAttributes));

            $form->large();

            $form->saved(function ($form) {
                return $this->modalSaveRespose($form, __('admin.Upload succeeded'));
            });
        });
    }

    public function browser(Request $request){
        $grid = new Grid(new $this->model());

        $grid->column('original')->display(function($value){
            return $this->path;
        });
        $grid->column('width');
        $grid->column('height');
        $grid->column('path', __('content.Image'))->image();
        

        $grid->column('title', __('content.Title'))->display(function($text) {
            return \Str::limit($text, 80, '...');
        });
        $grid->column('source', __('content.Source'));
        
        $grid->filter(function ($filter) {

            $filter->disableIdFilter();
            $filter->expand();

            $filter->column(1 / 2, function ($filter) {
                $filter->like('title', __('content.Title'));
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->like('source', __('content.Source'));
            });
        });

        $grid->setView('admin::grid.card-selectable');
        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();

        if (\Admin::user()->can($this->slug . '.create')){ //Ez az editorban lévő inlineImage miatt van itt
            $modalButton = new ModalButton(__('admin.upload'), route('admin.image.modal.form', ['rules' => 'dimensions:min_width='.config('image.rules.medium.minWidth').',min_height='.config('image.rules.medium.minHeight').'']));
            $modalButton->setClass('btn btn-primary btn-sm ml-5');

            $grid->tools(function ($tools) use ($modalButton) {
                $tools->append($modalButton);
            });
        }
        $grid->model()->orderBy('created_at', 'desc');

        return $grid->render();
    }

}
