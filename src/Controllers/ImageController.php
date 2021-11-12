<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Extensions\ModalForm\Form\ModalButton;
use Encore\Admin\Extensions\ModalForm\Form\ModalForm;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Models\Image;
use Encore\Admin\Show;
use Request;

class ImageController extends AdminController
{
    protected $model = Image::class;

    public function __construct()
    {
        $model = \Request()->get('im');

        if (!empty($model)){
            $model = base64_url_decode($model);
            if (class_exists($model)) $this->model = $model;
        }

        $this->minWidth = $this->model::getRules()['minWidth'];
        $this->minHeight = $this->model::getRules()['minHeight'];

        parent::__construct();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new $this->model());

        $grid->column('path', $this->model::label('path'))->image();

        $grid->column('title', $this->model::label('title'))->display(function ($text) {
            return \Str::limit($text, 150, '...');
        });
        $grid->column('source', $this->model::label('source'));

        $grid->filter(function ($filter) {

            $filter->disableIdFilter();

            $filter->where(function ($query) {

                $query->where('title', 'like', "%{$this->input}%")
                    ->orWhere('source', 'like', "%{$this->input}%")
                    ->orWhere('filename', 'like', "%{$this->input}%");
            }, __('admin.Search'));
        });

        $grid->setView('admin::grid.image-card');
 
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

        $show->field('path', $this->model::label('path'))->image();

        $show->field('title', $this->model::label('title'));
        $show->field('source', $this->model::label('source'));

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

    private function setForm(&$form)
    {

        $rules = request('rules');
        $help = request('help');

        $form->column(4, function ($form) use ($rules, $help) {
            $image =  $form->image('path', $this->model::label('path'))->rules($rules)->required();

            if (!empty($rules)) {
                $image->rules($rules);
            } else {
                $image->rules('dimensions:min_width=' . $this->minWidth . ',min_height=' . $this->minHeight . '');
            }

            if (!empty($help)) {
                $image->help($help);
            } else {
                $image->help(__('admin.imageSizeHelp', [
                    'width' => $this->minWidth,
                    'height' => $this->minHeight
                ]));
            }
        });

        $form->column(8, function ($form) {
            $form->text('title', $this->model::label('title'))->rules('max:700');
            $form->text('source', $this->model::label('source'))->rules('max:150');

            if ($form->model()::getWatermark()) {

                $form->switch('watermark', $this->model::label('watermark'))->states($this->getYesNoSwitch());
                $form->ignore('watermark');
            }
        });
        
        $form->setAction(url_query($form->builder()->getAction(), ['im' => base64_url_encode($this->model)]));

        return $form;
    }

    public function ModalFormSore()
    {
        return $this->ModalForm()->store();
    }

    public function ModalForm()
    {
        return new ModalForm(new $this->model(), function (ModalForm $form) {

            $this->setForm($form);

            $routeAttributes = [];

            $rules = request('rules');
            $help = request('help');

            if (!empty($rules)) $routeAttributes['rules'] = $rules;
            if (!empty($help)) $routeAttributes['help'] = $help;

            $oldAction = parse_url($form->builder()->getAction());
            if (!isset($oldAction['query'])) $oldAction['query'] = '';
            
            $form->setAction(url_query(route('admin.image.modal.form.store', $oldAction['query']), $routeAttributes));
            // $form->setAction(route('admin.image.modal.form.store', $routeAttributes));

            $form->large();

            $form->footer(function ($footer) {
                $footer->disableReset();
            });

            $form->saved(function ($form) {
                return $this->modalSaveRespose($form, __('admin.Upload succeeded'));
            });
        });
    }

    public function browser(Request $request) //Ckeditor
    {
        $grid = new Grid(new $this->model());

        $grid->column('original')->display(function ($value) {
            return $this->path;
        });
        $grid->column('width');
        $grid->column('height');
        $grid->column('path', $this->model::label('path'))->image();


        $grid->column('title', $this->model::label('title'))->display(function ($text) {
            return \Str::limit($text, 80, '...');
        });
        $grid->column('source', $this->model::label('source'));

        $grid->filter(function ($filter) {

            $filter->disableIdFilter();
            $filter->expand();

            $filter->where(function ($query) {

                $query->where('title', 'like', "%{$this->input}%")
                    ->orWhere('source', 'like', "%{$this->input}%")
                    ->orWhere('filename', 'like', "%{$this->input}%");
            }, __('admin.Search'));
        });

        $grid->setView('admin::grid.image-card-ckeditor');
        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();

        if (\Admin::user()->can($this->slug . '.create')) {
            $modalButton = new ModalButton(__('admin.new'), route('admin.image.modal.form', ['rules' => 'dimensions:min_width=' . $this->model::getRules()['minWidth'] . ',min_height=' . $this->model::getRules()['minHeight'] . '']));
            $modalButton->setClass('btn btn-primary btn-sm ml-5');

            $grid->tools(function ($tools) use ($modalButton) {
                $tools->append($modalButton);
            });
        }
        $grid->model()->orderBy('created_at', 'desc');

        return $grid->render();
    }
}
