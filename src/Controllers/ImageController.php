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

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new $this->model());

        $grid->column('path', __('image'))->image();

        $grid->column('title', __('title'))->display(function ($text) {
            return \Str::limit($text, 150, '...');
        });
        $grid->column('source', __('source'));

        $grid->filter(function ($filter) {

            $filter->disableIdFilter();

            $filter->where(function ($query) {

                $query->where('title', 'like', "%{$this->input}%")
                    ->orWhere('source', 'like', "%{$this->input}%");
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

        $show->field('path', __('image'))->image();

        $show->field('title', __('title'));
        $show->field('source', __('source'));

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
            $image =  $form->image('path', __('image'))->rules($rules)->required();

            if (!empty($rules)) {
                $image->rules($rules);
            }

            if (!empty($help)) {
                $image->help($help);
            }
        });

        $form->column(8, function ($form) {
            $form->text('title', __('title'))->rules('max:700');
            $form->text('source', __('source'))->rules('max:150');

            if (config('image.watermark')) {

                $form->switch('watermark', __('watermark'))->states($this->getYesNoSwitch());
                $form->ignore('watermark');
            }
        });

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

            $form->setAction(route('admin.image.modal.form.store', $routeAttributes));

            $form->large();

            $form->footer(function ($footer) {
                $footer->disableReset();
            });

            $form->saved(function ($form) {
                return $this->modalSaveRespose($form, __('admin.Upload succeeded'));
            });
        });
    }

    public function browser(Request $request)
    {
        $grid = new Grid(new $this->model());

        $grid->column('original')->display(function ($value) {
            return $this->path;
        });
        $grid->column('width');
        $grid->column('height');
        $grid->column('path', __('image'))->image();


        $grid->column('title', __('title'))->display(function ($text) {
            return \Str::limit($text, 80, '...');
        });
        $grid->column('source', __('source'));

        $grid->filter(function ($filter) {

            $filter->disableIdFilter();
            $filter->expand();

            $filter->where(function ($query) {

                $query->where('title', 'like', "%{$this->input}%")
                    ->orWhere('source', 'like', "%{$this->input}%");
            }, __('admin.Search'));
        });

        $grid->setView('admin::grid.image-card-ckeditor');
        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();

        if (\Admin::user()->can($this->slug . '.create')) {
            $modalButton = new ModalButton(__('admin.new'), route('admin.image.modal.form', ['rules' => 'dimensions:min_width=' . config('image.rules.medium.minWidth') . ',min_height=' . config('image.rules.medium.minHeight') . '']));
            $modalButton->setClass('btn btn-primary btn-sm ml-5');

            $grid->tools(function ($tools) use ($modalButton) {
                $tools->append($modalButton);
            });
        }
        $grid->model()->orderBy('created_at', 'desc');

        return $grid->render();
    }
}
