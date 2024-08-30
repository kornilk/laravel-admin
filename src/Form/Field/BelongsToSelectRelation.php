<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait BelongsToSelectRelation
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
     * @var string
     */
    protected $relationType;

    /**
     * Foreign key
     *
     * @var string
     */
    protected $otherKey;

    /**
     * Pivon key
     *
     * @var string
     */
    protected $otherPivotKey;

    public function __construct($column, $arguments = [])
    {
        $this->relationName = $column;
        $this->config('column', isset($arguments[1]) ? $arguments[1] : 'title');
        $this->config('minimumInputLength', isset($arguments[2]) ? $arguments[2] : 0);

        parent::__construct($column, $arguments);
    }

    protected function getLoadUrl()
    {
        return route('admin.getRelationSelectItem', [
            'model' => urlencode($this->relationModelClass),
            'primaryKey' => $this->otherKey,
            'column' => $this->config['column'],
        ]);
    }

    /**
     * Get other key for this many-to-many relation.
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getOtherKey($relation, $foreignNotPivot = false)
    {
        if (!$foreignNotPivot && $this->otherKey) {
            return $this->otherKey;
        }

        if ($foreignNotPivot && $this->otherPivotKey) {
            return $this->otherPivotKey;
        }

        if ($relation instanceof BelongsToMany) {
            /* @var BelongsToMany $relation */
            if ($foreignNotPivot) {
                return $this->otherKey = $relation->getRelated()->getKeyName();
            } else {
                $fullKey = $relation->getQualifiedRelatedPivotKeyName();
                $fullKeyArray = explode('.', $fullKey);

                return $this->otherKey = 'pivot.' . end($fullKeyArray);
            }
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

        $this->relationType = (new \ReflectionClass($relation))->getShortName();

        if (!array($this->relationType, [
            'BelongsTo', 'BelongsToMany', 'HasMany',
        ])) {
            throw new \InvalidArgumentException(
                "[Relation [{$this->relationName}] must be `BelongsToMany` or `HasMany` or `BelongsTo` type"
            );
        }

        $this->relationModel = $relation->getQuery()->getModel();
        $this->relationModelClass = get_class($this->relationModel);

        $relationModel = $this->relationModel;
        $relationTextColumn = $this->config['column'];

        $primaryKey = $this->getOtherKey($relation, $this->relationType === 'BelongsToMany');

        if ($relation instanceof BelongsTo) {
            $this->column = $relation->getForeignKeyName();
        }

       $column = $this->column;

        $this->options(function ($value) use ($relationModel, $column, $relationTextColumn, $primaryKey) {

            $oldValue = old($column);
            if (!empty($oldValue)) $value = $oldValue;
            
            if (empty($value)) return [];
            if (!is_array($value)) $value = [$value];

            $item = $relationModel::whereIn($primaryKey, $value)->get();

            return $item->pluck($relationTextColumn, $primaryKey);
        });

        $this->ajax(route('admin.getRelationSelectItems', [
            'model' => urlencode($this->relationModelClass),
            'primaryKey' => $primaryKey,
            'column' => $this->config['column'],
        ]), $primaryKey, $this->config['column']);

        return $this;
    }

    protected function addScript()
    {
        $formClass = $this->form->getFormClass();

        $script = <<<SCRIPT
        ;(function () {

            var formField = $('.{$formClass} .relation-selectable-{$this->column()}');
            var relationType = '{$this->relationType}';
            var input = $("{$this->getElementClassSelector()}");

            console.log(formField, input);

            var formResponse = function(e){
      
                var itemId = $(e.target).data('model-id');

                if (!(itemId instanceof Array)){
                    itemId = [itemId];
                }

                for (var i = 0; i < itemId.length; i++) {
                    $.get("{$this->getLoadUrl()}?id=" + itemId[i], function(response){

                        if (response?.data?.id) {
                            var newOption = new Option(response.data.text, response.data.id, true, true);
                            input.append(newOption);
                            
                            var selected = input.val() || [];

                            if (!isNaN(selected) && typeof parseInt(selected) === 'number') {
                                selected = [selected];
                            }

                            if (relationType === 'BelongsToMany') {
                                selected.push(response.data.id);
                            } else {
                                selected = [response.data.id];
                            }

                            input.val(selected).trigger('change');   
                        }

                    });
                }
 
            }

            formField.find('a[data-form="modal"]').on('formResponse', formResponse);
        
            
        })();
        SCRIPT;

        Admin::script($script);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $modalButton = null;

        if (Admin::user()->can("{$this->relationModel->getContentPermissionName()}.create")) {
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
