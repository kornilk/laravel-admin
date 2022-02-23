<?php

namespace Encore\Admin\Grid;

use Encore\Admin\Grid;
use Encore\Admin\Grid\Selectable\Checkbox;
use Encore\Admin\Grid\Selectable\Radio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @mixin Grid
 */
abstract class Selectable
{
    /**
     * @var string
     */
    public $model;

    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @var string
     */
    protected $key = '';

    /**
     * @var bool
     */
    protected $multiple = false;

    /**
     * @var int
     */
    protected $perPage = 10;

    /**
     * @var bool
     */
    protected $imageLayout = false;

    protected $editable = false;
    protected $disableSelectButton = false;

    /**
     * Selectable constructor.
     *
     * @param $key
     * @param $multiple
     */
    public function __construct($multiple = false, $key = '')
    {
        $this->key = $key ?: $this->key;
        $this->multiple = $multiple;

        $this->initGrid();
    }

    /**
     * @return void
     */
    abstract public function make();

    protected function imageLayout()
    {
        $this->imageLayout = true;
    }

    /**
     * @param bool $multiple
     *
     * @return string
     */
    public function render()
    {
        $this->make();

        if ($this->imageLayout) {
            $this->setView('admin::grid.image', ['key' => $this->key]);
        } else {

            if ($this->editable) {
                $this->appendEditBtn(true);
            }

            $this->appendRemoveBtn(true);
        }

        $this->disableFeatures()->paginate($this->perPage)->expandFilter();

        $displayer = $this->multiple ? Checkbox::class : Radio::class;

        $this->prependColumn('__modal_selector__', ' ')->displayUsing($displayer, [$this->key]);

        return $this->grid->render();
    }

    /**
     * @return $this
     */
    protected function disableFeatures()
    {
        return $this->disableExport()
            ->disableActions()
            ->disableBatchActions()
            ->disableCreateButton()
            ->disableColumnSelector()
            ->disablePerPageSelector();
    }

    public function disableSelectButton($boolean = true)
    {
        $this->disableSelectButton = $boolean;
    }

    public function renderFormGrid($values)
    {
        $this->make();

        if ($this->editable) {
            $this->appendEditBtn(false);
        }

        $this->appendRemoveBtn(false);

        $this->model()->whereKey(Arr::wrap($values));

        $this->disableFeatures()->disableFilter();

        if (!$this->multiple) {
            $this->disablePagination();
        }

        if (!$this->disableSelectButton) {
            $this->tools(function (Tools $tools) {
                $tools->append(new Grid\Selectable\BrowserBtn());
            });
        }

        return $this->grid;
    }

    protected function appendRemoveBtn($hide = true)
    {
        $hide = $hide ? 'hide' : '';
        $key = $this->key;

        $this->column('__remove__', ' ')->display(function () use ($hide, $key) {
            return <<<BTN
<a href="javascript:void(0);" class="grid-row-remove {$hide}" data-key="{$this->getAttribute($key)}">
    <i class="fa fa-trash"></i>
</a>
BTN;
        })->setAttributes(['style' => 'width:25px']);;
    }

    protected function appendEditBtn($hide = true)
    {
        $hide = $hide ? 'hide' : '';
        $key = $this->key;
        $editRouteName = $this->getEditRouteName();

        $this->column('__edit__', ' ')->display(function () use ($hide, $key, $editRouteName) {
            $url = \Route::has($editRouteName) ? route($editRouteName, ['id' => $this->getAttribute($key)]) : '#';
            return <<<BTN
<a href="{$url}" data-form="modal" class="grid-row-edit {$hide}" data-key="{$this->getAttribute($key)}">
    <i class="fa fa-pencil-square-o"></i>
</a>
BTN;
        })->setAttributes(['style' => 'width:25px']);
    }

    protected function getEditRouteName()
    {

        if (property_exists($this, 'model') && method_exists($this->model, 'getContentSlug')) {
            return "admin.{$this->model::getContentSlug()}.edit.modal";
        }

        return null;
    }

    protected function renderModalCreateButton()
    {
        if (\Admin::user()->can("{$this->getModelPermissionName()}.create")) {
            $modalButton = new \Encore\Admin\Extensions\ModalForm\Form\ModalButton(__('admin.new'), route("admin.{$this->getModelSlug()}.create.modal"));
            $modalButton->setClass('btn btn-primary btn-sm ml-5');
            $this->tools(function ($tools) use ($modalButton) {
                $tools->append($modalButton);
            });
        }
    }

    protected function getModelPermissionName()
    {
        return property_exists($this, 'model') && method_exists($this->model, 'getContentPermissionName') ? $this->model::getContentPermissionName() : null;
    }

    protected function getModelSlug()
    {
        return property_exists($this, 'model') && method_exists($this->model, 'getContentSlug') ? $this->model::getContentSlug() : null;
    }

    protected function initGrid()
    {
        if (!class_exists($this->model) || !is_subclass_of($this->model, Model::class)) {
            throw new \InvalidArgumentException("Invalid model [{$this->model}]");
        }

        /** @var Model $model */
        $model = new $this->model();

        $this->grid = new Grid(new $model());

        if (!$this->key) {
            $this->key = $model->getKeyName();
        }
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        return $this->grid->{$method}(...$arguments);
    }
}
