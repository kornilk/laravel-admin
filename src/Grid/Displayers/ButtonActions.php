<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\Actions;

class ButtonActions extends Actions
{
    /**
     * Options default.
     *
     * @return array
     */
    private $optionsDefault = [
        'view' => [
            'label' => '',
            'icon'  => 'fa fa-eye',
            'class' => 'btn btn-sm btn-primary',
        ],
        'edit' => [
            'label' => '',
            'icon'  => 'fa fa-edit',
            'class' => 'btn btn-sm btn-success',
        ],
        'delete' => [
            'label' => '',
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
     * Render delete action.
     *
     * @return string
     */
    protected function renderDelete()
    {
        $this->setupDeleteScript();
        $options = $this->getOptions('delete');

        return <<<EOT
<a href="javascript:void(0);" data-id="{$this->getKey()}" class="{$this->actionButtonsClass} {$this->grid->getGridRowName()}-delete {$options['class']}">
    <i class="{$options['icon']}"></i> {$options['label']}
</a>
EOT;
    }

    /**
     * Render edit action.
     *
     * @return string
     */
    protected function renderEdit()
    {
        $options = $this->getOptions('edit');

        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}/edit" class="{$this->actionButtonsClass} {$this->grid->getGridRowName()}-edit {$options['class']}">
     <i class="{$options['icon']}"></i> {$options['label']}
</a>
EOT;
    }

    /**
     * Render view action.
     *
     * @return string
     */
    protected function renderView()
    {
        $options = $this->getOptions('view');

        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}" class="{$this->actionButtonsClass} {$this->grid->getGridRowName()}-view {$options['class']}">
     <i class="{$options['icon']}"></i> {$options['label']}
</a>
EOT;
    }
}