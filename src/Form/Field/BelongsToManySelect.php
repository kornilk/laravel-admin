<?php

namespace Encore\Admin\Form\Field;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BelongsToManySelect extends Select
{
    use BelongsToSelectRelation;

    public function fill($data)
    {
        if ($this->form && $this->form->shouldSnakeAttributes()) {
            $key = Str::snake($this->column);
        } else {
            $key = $this->column;
        }

        $relations = Arr::get($data, $key);
   
        if (is_string($relations)) {
            $this->value = explode(',', $relations);
        }

        if (!is_array($relations)) {
            $this->applyCascadeConditions();

            return;
        }

        $first = current($relations);
     
        if (is_null($first)) {
            $this->value = null;

            // MultipleSelect value store as an ont-to-many relationship.
        } elseif (is_array($first)) {
      
            foreach ($relations as $relation) {
                $this->value[] = Arr::get($relation, $this->getOtherKey($relation));
            }

            // MultipleSelect value store as a column.
        } else {
            $this->value = $relations;
        }

        $this->applyCascadeConditions();
    }

    public function setOriginal($data)
    {
        $relations = Arr::get($data, $this->column);

        if (is_string($relations)) {
            $this->original = explode(',', $relations);
        }

        if (!is_array($relations)) {
            return;
        }

        $first = current($relations);

        if (is_null($first)) {
            $this->original = null;

            // MultipleSelect value store as an ont-to-many relationship.
        } elseif (is_array($first)) {
            foreach ($relations as $relation) {
                $this->original[] = Arr::get($relation, $this->getOtherKey($relation));
            }

            // MultipleSelect value store as a column.
        } else {
            $this->original = $relations;
        }
    }

    public function prepare($value)
    {
        $value = (array) $value;

        return array_filter($value, 'strlen');
    }

}
