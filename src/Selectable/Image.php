<?php

namespace Encore\Admin\Selectable;

use Encore\Admin\Extensions\ModalForm\Form\ModalButton;
use Encore\Admin\Models\Image as ImageModel;
use Encore\Admin\Grid\Selectable;

class Image extends Selectable
{
    public $model = ImageModel::class;
    protected $modalButtonAttributes = [];
    // protected $minWidth = 0;
    // protected $minHeight = 0;

    public function __construct($multiple = false, $key = '')
    {
        // $this->minWidth = $this->model::getRules()['minWidth'];
        // $this->minHeight = $this->model::getRules()['minHeight'];

        $this->setModalButtonAttributes();
        parent::__construct($multiple, $key);

    }

    public function setModalButtonAttributes()
    {
        // $this->modalButtonAttributes = ['rules' => 'dimensions:min_width=' . $this->minWidth . ',min_height=' . $this->minHeight . ''];

        // $this->modalButtonAttributes['help'] = __('admin.imageSizeHelp', [
        //     'width' => $this->minWidth,
        //     'height' => $this->minHeight
        // ]);

        $this->modalButtonAttributes['im'] = base64_url_encode($this->model);
    }

    public function make()
    {
        $this->column('path', $this->model::label('path'))->setAttributes(['class' => 'hideLabel'])->image('', false);

        $this->column('title', $this->model::label('title'))->setAttributes(['class' => 'hideLabel'])->display(function ($value) {
            return \Str::limit($value, 100, '...');
        });

        $this->filter(function ($filter) {

            $filter->where(function ($query) {

                $query->where('title', 'like', "%{$this->input}%")
                    ->orWhere('source', 'like', "%{$this->input}%")
                    ->orWhere('filename', 'like', "%{$this->input}%");
            }, __('admin.Search'));
        });

        $this->renderModalCreateButton($this->modalButtonAttributes);

        $this->model()->orderBy('created_at', 'desc');

        $this->setView('admin::grid.image-card');

    }
}
