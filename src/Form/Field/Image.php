<?php

namespace Encore\Admin\Form\Field;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Image as ImageClass;

class Image extends File
{
    use ImageField;

    /**
     * {@inheritdoc}
     */
    protected $view = 'admin::form.file';

    /**
     *  Validation rules.
     *
     * @var string
     */
    protected $rules = 'image';

    public function __construct($column = '', $arguments = [])
    {
        $this->retainable();
        $this->orientate();
        $this->attribute(['accept' => 'image/*',  'capture' => 'camera']);

        parent::__construct($column, $arguments);
    }

    /**
     * @param array|UploadedFile $image
     *
     * @return string
     */
    public function prepare($image)
    {
        $interventionMethods = [];
        foreach ($this->interventionCalls as $int){
            $interventionMethods[] = $int['method'];
        }

        if (!in_array('thumbnail', $interventionMethods)){
            $this->thumbnail($this->form->model()::getThumbnails());
        }

        if (!in_array('widen', $interventionMethods) && !empty($maxSize = $this->form->model()::getMaxSize())){
            $this->widen($maxSize, function ($constraint) {
                $constraint->upsize();
            });
        }

        if (request()->has('watermark') && request()->watermark === 'on') {

            try {
                $width = ImageClass::make($image)->width();

                if (!empty(($maxSize = $this->form->model()::getMaxSize())) && $width > $maxSize) $width = $maxSize;

                $watermarkWidth = round($width / 10);

                if ($watermarkWidth < 150) $watermarkWidth = 150;

                if ($width < $watermarkWidth) $watermarkWidth = $width;

                $offset = 15;
                switch (true) {

                    case $width >= 1000:
                        $offset = 50;
                        break;
                }

                $watermark = ImageClass::make($this->form->model()::getWatermark())->widen($watermarkWidth, function ($constraint) {
                    $constraint->upsize();
                });

                $this->insert($watermark, 'bottom-right', $offset, $offset);
            } catch (\Throwable $th) {
            }
        }

        if ($this->picker) {
            return parent::prepare($image);
        }

        if (request()->has(static::FILE_DELETE_FLAG)) {
            return $this->destroy();
        }

        $this->name = $this->getStoreName($image);
        $this->name = \Str::camel($this->name);
        $this->renameIfExists($image);
        
        if ($this->form->model()::getKeepOriginal()) {
            $pi = pathinfo($this->name);
            $this->uploadWithFilename($image, $pi['filename'] . '-original.' . $pi['extension']);
        }

        $this->callInterventionMethods($image->getRealPath());

        $path = $this->uploadWithFilename($image, $this->name);

        $this->destroy();

        $this->uploadAndDeleteOriginalThumbnail($image);

        return $path;
    }

    protected function uploadWithFilename(UploadedFile $file, $name)
    {
        $path = null;

        if (!is_null($this->storagePermission)) {
            $path = $this->storage->putFileAs($this->getDirectory(), $file, $name, $this->storagePermission);
        } else {
            $path = $this->storage->putFileAs($this->getDirectory(), $file, $name);
        }

        return $path;
    }

    /**
     * force file type to image.
     *
     * @param $file
     *
     * @return array|bool|int[]|string[]
     */
    public function guessPreviewType($file)
    {
        $extra = parent::guessPreviewType($file);
        $extra['type'] = 'image';

        return $extra;
    }

    public function setImageSize(string $value)
    {
        $this->imageSize = $value;

        return $this;
    }
}
