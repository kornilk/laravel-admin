<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Form\Textarea;

class Editor extends Textarea
{
    protected static $js = [
        '//cdn.ckeditor.com/4.5.10/standard/ckeditor.js',
    ];

    public function render()
    {
        $config = (array) CKEditor::config('config');

        $config = json_encode(array_merge($config, $this->options));
        $selector = getSelectorFromForm($this->form);
        
        $this->script = <<<EOT
            var selector = $('{$selector}[name="{$this->id}"]');

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
