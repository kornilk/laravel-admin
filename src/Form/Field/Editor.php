<?php

namespace Encore\Admin\Form\Field;

class Editor extends Textarea
{
    protected static $js = [
        'vendor/laravel-admin/ckeditor/ckeditor.js',
    ];

    public function __construct($column = '', $arguments = [])
    {
        parent::__construct($column, $arguments);
        $this->setElementClass('ckEditorTextarea');
        $this->id = $this->formatId(uniqid() . "-{$column}");
    }

    public function render()
    {
        $config = (array) config('admin.extensions.ckeditor.config');

        $config = json_encode(array_merge($config, $this->options));
        $formClass = $this->form->getFormClass();
        
        $this->script = <<<EOT
            var selector = $('.{$formClass} #{$this->id}');

            for (var prop in CKEDITOR.instances){
                if (CKEDITOR.instances[prop].element.$ == selector[0]){
                    CKEDITOR.instances[prop].destroy(); 
                }
            }

            var editor = CKEDITOR.replace(selector[0], $config);
            selector.data('editor', editor);
        EOT;

        return parent::render();
    }
}
