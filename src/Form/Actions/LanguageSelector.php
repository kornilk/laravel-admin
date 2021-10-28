<?php

namespace Encore\Admin\Form\Actions;
use Illuminate\Contracts\Support\Renderable;

class LanguageSelector implements Renderable
{
    protected $form;

    public function __construct($form)
    {
        $this->form = $form;
        $this->formClass = $form->getFormClass();
    }

    public function render()
    {
        $this->renderScript();

        $buttons = [];

        foreach (config('i18n.locales') as $locale) {
            $buttons[] = '<button data-locale="'.$locale.'" type="button" class="btn btn-sm btn-default ' . ($locale === config('i18n.default') ? 'active btn-success' : '') . '" title="'.$locale.'">'.strtoupper($locale).'</button>';
        }

        $buttonGroup = '<div data-form="'.$this->formClass.'" class="btn-group pull-right languageFormSelector" style="margin-right: 5px">'.implode('',$buttons).'</div>';

        return $buttonGroup;
    }

    protected function renderScript(){
$script = <<<SCRIPT

$('.languageFormSelector[data-form="{$this->formClass}"] button').off().on('click', function(){

    var form = $('form.{$this->formClass}');

    if ($(this).hasClass('.active')) return;
    $(this).parent().find('button').removeClass('active btn-success');
    $(this).addClass('active btn-success');

    form.find('.translatable').addClass('translatable-hidden');
    form.find('.translatable[data-locale="'+$(this).data('locale')+'"]').removeClass('translatable-hidden');

});

SCRIPT;
        \Admin::script($script);
    }
}