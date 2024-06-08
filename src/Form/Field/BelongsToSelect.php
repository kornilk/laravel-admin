<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Admin;
use Encore\Admin\Form;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BelongsToSelect extends Select
{
    /**
     * @var object
     */
    protected $relationModel;

    /**
     * @var string
     */
    protected $relationModelClass;

    /**
     * @var string
     */
    protected $relationName;

    /**
     * Other key for many-to-many relation.
     *
     * @var string
     */
    protected $otherKey;

    public function __construct($column, $arguments = [])
    {
        $this->relationName = $column;
        $this->config('column', isset($arguments[1]) ? $arguments[1] : 'title');

        parent::__construct($column, $arguments);
    }

    protected function addScript()
    {
        $formClass = $this->form->getFormClass();

        $script = <<<SCRIPT
        ;(function () {

            var formField = $('.{$formClass} .relation-selectable-{$this->column()}');
      
            var formResponse = function(e){
      
                var itemId = $(e.target).data('model-id');

                $.get("{$this->getLoadUrl()}?id=" + itemId, function(response){

                    if (response?.data?.text){
                        var input = $("{$this->getElementClassSelector()}");
                   
                        input
                        .select2({data: [response?.data]})
                        .val([itemId])
                        .trigger('change');   
                    }
                });
 
            }

            formField.find('a[data-form="modal"]').on('formResponse', formResponse);
        
            
        })();
        SCRIPT;

        Admin::script($script);

        return $this;
    }

      /**
     * @param int $multiple
     *
     * @return string
     */
    protected function getLoadUrl()
    {
        return route('admin.getRelationSelectItem', [
            'model' => urlencode($this->relationModelClass),
            'primaryKey' => $this->otherKey,
            'column' => $this->config['column'],
        ]);
    }

    /**
     * @param Form $form
     *
     * @return $this
     */
    public function setForm(Form $form = null)
    {
        $this->form = $form;

        $model = $this->form->model();

        $relation = $model->{$this->relationName}();

        if (!is_object($relation)) {
            throw new \InvalidArgumentException(
                "[[{$this->relationName}] is not a relation"
            );
        }

        $relationType = (new \ReflectionClass($relation))->getShortName();

        if (!array($relationType, [
            'BelongsTo', 'BelongsToMany', 'HasMany',
        ])) {
            throw new \InvalidArgumentException(
                "[Relation [{$this->relationName}] must be `BelongsToMany` or `HasMany` or `BelongsTo` type"
            );
        }

        $this->relationModel = $relation->getQuery()->getModel();
        $this->relationModelClass = get_class($this->relationModel);

        $relationModel = $this->relationModel;
        $column = $this->config['column'];

        $primaryKey = $this->getOtherKey($relation);

        if ($relation instanceof BelongsTo) {
            $this->column = $relation->getForeignKeyName();
        }



        $this->options(function ($value) use ($relationModel, $column, $primaryKey) {

            if (empty($value)) return [];
            if (!is_array($value)) $value = [$value];

            $item = $relationModel::whereIn($primaryKey, $value)->get();

            return $item->pluck($column, $primaryKey);
        });

        $this->ajax(route('admin.getRelationSelectItems', [
            'model' => urlencode($this->relationModelClass),
            'primaryKey' => $primaryKey,
            'column' => $this->config['column'],
        ]), $primaryKey, $this->config['column']);

        return $this;
    }

    /**
     * Get other key for this many-to-many relation.
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getOtherKey($relation)
    {
        if ($this->otherKey) {
            return $this->otherKey;
        }

        if ($relation instanceof BelongsToMany) {
            /* @var BelongsToMany $relation */
            $fullKey = $relation->getQualifiedRelatedPivotKeyName();
            $fullKeyArray = explode('.', $fullKey);

            return $this->otherKey = 'pivot.' . end($fullKeyArray);
        } elseif ($relation instanceof HasMany) {
            /* @var HasMany $relation */
            return $this->otherKey = $relation->getRelated()->getKeyName();
        } elseif ($relation instanceof BelongsTo) {
            /* @var BelongsTo $relation */
            return $this->otherKey = $relation->getRelated()->getKeyName();
        }

        throw new \Exception('Column of this field must be a `BelongsToMany` or `HasMany` or `BelongsTo` relation.');
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $modalButton = null;

        if (\Admin::user()->can("{$this->relationModel->getContentPermissionName()}.create")) {
            $modalButton = new \Encore\Admin\Extensions\ModalForm\Form\ModalButton('<i class="fa fa-plus"></i>', route("admin.{$this->relationModel->getContentSlug()}.create.modal", []));
            $modalButton->setClass('btn btn-sm btn-success');
            $this->addScript();
        }

        $this->addVariables([
            'modalButton' => $modalButton,
        ]);

        return parent::render();
    }
}
