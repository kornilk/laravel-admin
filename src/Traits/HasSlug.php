<?php

namespace Encore\Admin\Traits;

trait HasSlug
{

    protected $slugColumn = 'slug';
    protected $slugFromColumn = 'title';

    function generateSlug(string $value, $id): string
    {
        $count = 0;
        $original = \Str::slug($value);
        $value = $original;

        $query = self::where($this->slugColumn, $value)->withTrashed();
        if (!empty($id)) $query->where('id', '!=', $id);

        while ($query->count() > 0) {
            $count++;
            $value = $original . '-' . $count;

            $query = self::where($this->slugColumn, $value)->withTrashed();
            if (!empty($id)) $query->where('id', '!=', $id);
        }

        return $value;
    }

    function generateTranslatedSlug(string $value, $id, $locale): string
    {
        $count = 0;
        $original = \Str::slug($value);
        $value = $original;

        $query = self::whereTranslation($this->slugColumn, '=', $value, [$locale], false)->where('id', '!=', $id)->withTrashed();

        while ($query->count() > 0) {
            $count++;
            $value = $original . '-' . $count;

            $query = self::whereTranslation($this->slugColumn, '=', $value, [$locale], false)->where('id', '!=', $id)->withTrashed();
        }

        return $value;
    }

    public static function bootHasSlug()
    {
        static::saving(function ($model) {
            if (empty($model->{$model->slugColumn})) {
                if (!empty($model->{$model->slugFromColumn}))
                    $model->{$model->slugColumn} = $model->generateSlug($model->{$model->slugFromColumn}, $model->id);
            } else {
                if (!empty($model->{$model->slugColumn}))
                    $model->{$model->slugColumn} = $model->generateSlug($model->{$model->slugColumn}, $model->id);
            }
        });

        static::saved(function ($model) {

            //$model->saveTranslatableSlug($model);

        });
    }

    protected function saveTranslatableSlug()
    {
        if (translatable($this) && in_array($this->slugColumn, $this->getTranslatableAttributes())) {
            foreach (config('i18n.locales') as $locale) {
                if ($locale === config('i18n.default')) continue;

                $from = $this->{$this->slugFromColumn};

                $translator = $this->translate($locale);

                if (in_array($this->slugFromColumn, $this->getTranslatableAttributes())) {
                    $from = $translator->translationAttributeExists($this->slugFromColumn) ? $translator->{$this->slugFromColumn} : null;
                }

                if ($translator->translationAttributeExists($this->slugColumn) && !empty($slug = $translator->{$this->slugColumn})) {
                    $this->setTranslation($this->slugColumn, $locale, $this->generateTranslatedSlug($slug, $this->id, $locale), true);
                } else if (!empty($from)) {
                    $this->setTranslation($this->slugColumn, $locale, $this->generateTranslatedSlug($from, $this->id, $locale), true);
                }
            }
        }
    }
}
