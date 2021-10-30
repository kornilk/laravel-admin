<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Form\Textarea;

class Editor extends Textarea
{
    protected static $js = [
        'vendor/laravel-admin-ext/ckeditor/ckeditor.js',
    ];

    public function render()
    {
        $config = (array) CKEditor::config('config');

        $config = json_encode(array_merge($config, $this->options));
        $formClass = $this->form->getFormClass();
        
        $this->script = <<<EOT
            var selector = $('{$formClass} [name="{$this->id}"]');

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
