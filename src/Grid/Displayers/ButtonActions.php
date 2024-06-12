<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Actions\RowAction;
use Encore\Admin\Grid\Actions\Delete;
use Encore\Admin\Grid\Actions\Edit;
use Encore\Admin\Grid\Actions\Show;
use Encore\Admin\Grid\Displayers\Actions;
use Encore\Admin\Grid\Actions\ForceDelete;
use Encore\Admin\Grid\Actions\Restore;

class ButtonActions extends Actions
{
    /**
     * @var array
     */
    protected $custom = [];

    /**
     * @var array
     */
    protected $default = [];

    /**
     * @var array
     */
    protected $defaultClass = [Show::class, Edit::class, Delete::class];

    /**
     * Options default.
     *
     * @return array
     */
    private $optionsDefault = [
        'show' => [
            'icon'  => 'fa fa-eye',
            'class' => 'btn btn-sm btn-primary',
        ],
        'edit' => [
            'icon'  => 'fa fa-edit',
            'class' => 'btn btn-sm btn-success',
        ],
        'delete' => [
            'icon'  => 'fa fa-trash',
            'class' => 'btn btn-sm btn-danger text-white',
        ],
        'restore' => [
            'icon'  => 'fa fa-reply',
            'class' => 'btn btn-sm btn-primary',
        ],
        'forceDelete' => [
            'icon'  => 'fa fa-trash',
            'class' => 'btn btn-sm btn-danger text-white',
        ],
    ];

    private $actionButtonsClass = 'button-style-actions';

    /**
     * GET Config Options.
     *
     * @return array
     */
    private function getOptions($name)
    {
        return array_merge($this->optionsDefault[$name], (array) config("admin.custom-actions-button.{$name}"));
    }

    /**
     * @param RowAction $action
     *
     * @return $this
     */
    public function add(RowAction $action)
    {
        $this->prepareAction($action);

        array_push($this->custom, $action);

        return $this;
    }

    /**
     * @param RowAction $action
     */
    protected function prepareAction(RowAction $action)
    {
        $action->setGrid($this->grid)
            ->setColumn($this->column)
            ->setRow($this->row);
    }

    /**
     * Prepend default `edit` `view` `delete` actions.
     */
    protected function prependDefaultActions()
    {
        foreach ($this->defaultClass as $class) {
            /** @var RowAction $action */
            $action = new $class();

            $this->prepareAction($action);

            array_push($this->default, $action);
        }
    }

    /**
     * Disable view action.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableView(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->defaultClass, Show::class);
        } elseif (!in_array(Show::class, $this->defaultClass)) {
            array_push($this->defaultClass, Show::class);
        }

        return $this;
    }

    /**
     * Disable delete.
     *
     * @param bool $disable
     *
     * @return $this.
     */
    public function disableDelete(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->defaultClass, Delete::class);
        } elseif (!in_array(Delete::class, $this->defaultClass)) {
            array_push($this->defaultClass, Delete::class);
        }

        return $this;
    }

    /**
     * Disable Restore.
     *
     * @param bool $disable
     *
     * @return $this.
     */
    public function disableRestore(bool $disable = true)
    {

        if ($disable) {
            $custom = [];
            foreach ($this->custom as $action) {
                if ($action instanceof Restore) continue;
                $custom[] = $action;
            }
            $this->custom = $custom;
        } elseif (!in_array(Restore::class, $this->custom)) {
            array_push($this->custom, Restore::class);
        }

        return $this;
    }

    /**
     * Disable Force Restore.
     *
     * @param bool $disable
     *
     * @return $this.
     */
    public function disableForceDelete(bool $disable = true)
    {
        if ($disable) {
            $custom = [];
            foreach ($this->custom as $action) {
                if ($action instanceof ForceDelete) continue;
                $custom[] = $action;
            }
            $this->custom = $custom;
        } elseif (!in_array(ForceDelete::class, $this->custom)) {
            array_push($this->custom, ForceDelete::class);
        }

        return $this;
    }

    /**
     * Disable edit.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableEdit(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->defaultClass, Edit::class);
        } elseif (!in_array(Edit::class, $this->defaultClass)) {
            array_push($this->defaultClass, Edit::class);
        }

        return $this;
    }

    protected function renderButton($action){
        $options = $this->getOptions(lcfirst(class_basename($action)));
//         <a href="{$this->getResource()}/{$this->getRouteKey()}" class="{$this->actionButtonsClass} {$this->grid->getGridRowName()}-view {$options['class']}">
//         <i class="{$options['icon']}"></i> {$options['label']}
//    </a>
   $action->addElementClass("{$this->actionButtonsClass} {$this->grid->getGridRowName()}-view {$options['class']}");
   $action->attribute('title', $action->name());
   
   $action->setFormatName(function($name) use($options){
        return "<i class='{$options['icon']}'></i>";
   });
   return $action->render();
    }

    /**
     * @param null|\Closure $callback
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function display($callbacks = null)
    {
        $callbacks = func_get_args();

        foreach ($callbacks as $callback) {
            if ($callback instanceof \Closure) {
                $callback->call($this, $this);
            }
        }

        if ($this->disableAll) {
            return '';
        }

        $this->prependDefaultActions();

        $actions = $this->prepends;

        foreach ($this->default as $action) {
            array_push($actions,  $this->renderButton($action));
        }

        foreach ($this->custom as $action) {
            array_push($actions,  $this->renderButton($action));
        }

        $actions = array_merge($actions, $this->appends);

        return implode('', $actions);

    }
}
