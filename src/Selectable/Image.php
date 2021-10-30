<?php

namespace Encore\Admin\Selectable;

use Encore\Admin\Extensions\ModalForm\Form\ModalButton;
use Encore\Admin\Models\Image as ImageModel;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class Image extends Selectable
{
    public $model = ImageModel::class;
    protected $modalButtonAttributes = [];
    protected $minWidth = 0;
    protected $minHeight = 0;

    public function __construct($multiple = false, $key = '')
    {
        $this->minWidth = config('image.rules.medium.minWidth');
        $this->minHeight = config('image.rules.medium.minHeight');

        $this->setModalButtonAttributes();
        parent::__construct($multiple, $key);

    }

    public function setModalButtonAttributes()
    {
        $this->modalButtonAttributes = ['rules' => 'dimensions:min_width=' . $this->minWidth . ',min_height=' . $this->minHeight . ''];

        $this->modalButtonAttributes['help'] = __('admin.imageSizeHelp', [
            'width' => $this->minWidth,
            'height' => $this->minHeight
        ]);
    }

    public function make()
    {
        $this->column('path', __('content.Image'))->setAttributes(['class' => 'hideLabel'])->image('', false);

        $this->column('title', __('content.Title'))->setAttributes(['class' => 'hideLabel'])->display(function ($value) {
            return \Str::limit($value, 100, '...');
        });

        $this->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('title',  __('content.Title'));
            $filter->like('source', __('content.Source'));
        });

        if (\Admin::user()->can('images.create')){
            $modalButton = new ModalButton(__('admin.new'), route('admin.image.modal.form', $this->modalButtonAttributes));
            $modalButton->setClass('btn btn-primary btn-sm ml-5');
            $this->tools(function ($tools) use ($modalButton) {
                $tools->append($modalButton);
            });
        }

        $this->model()->orderBy('created_at', 'desc');

        $this->setView('admin::grid.image-card');

    }
}
