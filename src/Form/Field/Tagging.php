<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Form;

class Tagging extends MultipleSelect
{
    public $view = 'admin::form.multipleselect';
    protected $tagClass;
    protected $tagModel;

    public function __construct($column, $arguments = [])
    {
        $this->config('tags', true);
        $this->config('column', 'name');

        parent::__construct($column, array_slice($arguments, 1));
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

        $relation = $model->{$this->column}();

        if (!is_object($relation)) {
            throw new \InvalidArgumentException(
                "[[{$this->column}] is not a relation"
            );
        }

        $relationType = (new \ReflectionClass($relation))->getShortName();

        if ($relationType !== 'BelongsToMany') {
            throw new \InvalidArgumentException(
                "[Relation [{$this->column}] must be BelongsToMany type"
            );
        }

        $this->tagModel = $relation->getQuery()->getModel();
        $this->tagClass = get_class($this->tagModel);

        $tagModel = $this->tagModel;
        $column = $this->config['column'];
   
        $this->options(function ($values) use($tagModel, $column) {

            if (empty($values)) return [];

            $tags = $tagModel::whereIn('id', $values)->get();

            return $tags->pluck($column, 'id');
        });

        $this->ajax('/' . config('admin.route.prefix') . '/ajax/tagging/' . urlencode($this->tagClass), 'id', $this->config['column']);

        return $this;
    }

    public function prepare($value)
    {
        $value = (array) $value;
        
        if (is_array($value)){
            foreach ($value as &$v) {

                if (empty($v)) continue;

                if (!is_numeric($v)){
                    if (!$this->tagModel::where($this->config['column'], $v)->exists()){

                        $tag = new $this->tagModel();
                        $tag->{$this->config['column']} = $v;
                        $tag->save();
                        $v = $tag->id;
                    }
                }
            }
        }

        return array_filter($value, 'strlen');
    }
}