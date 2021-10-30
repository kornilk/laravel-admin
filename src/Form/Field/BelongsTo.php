<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Admin;

class BelongsTo extends Select
{
    use BelongsToRelation;

    protected function addScript()
    {
        $formClass = $this->form->getFormClass();
  
        $script = <<<SCRIPT
        ;(function () {

            var grid = $('{$formClass} .belongsto-{$this->column()}');
            var modal = $('#{$this->modalID}');
            var container = grid.find('.selectable-container');
            var selected = $("{$this->getElementClassSelector()}").val();
            var item = null;

            var emptyElement = $(grid.find('template.empty').html());

            if (container.prop('nodeName') !== 'TABLE') {
                emptyElement = emptyElement.find('.empty-grid');
            }

            // open modal
            grid.find('.select-relation').click(function (e) {
        
                modal.off().on('show.bs.modal', function (e) {
                    load("{$this->getLoadUrl()}");
                }).on('hidden.bs.modal', function (e) {
                    if ($('body .wrapper>.modal').length > 0) $('body').addClass('modal-open');
                }).on('click', '.page-item a, .filter-box a', function (e) {
                    load($(this).attr('href'));
                    e.preventDefault();
                }).on('click', '.selectable-item', function (e) {
                    $(this).find('input.select').iCheck('toggle');
                    e.preventDefault();
                }).on('submit', '.box-header form', function (e) {
                    load($(this).attr('action')+'&'+$(this).serialize());
                    return false;
                }).on('ifChecked', 'input.select', function (e) {
                    item = $(e.target).closest('.selectable-item');
                    selected = $(this).val();
                }).find('.modal-footer .submit').off().on('click', function () {
                    $("{$this->getElementClassSelector()}")
                        .select2({data: [selected]})
                        .val(selected)
                        .trigger('change')
                        .next()
                        .addClass('hide');
        
                    if (item) {
                        item.find('.grid-row-remove').removeClass('hide');
                        item.find('.column-__modal_selector__').remove();
                        container.empty().append(item);
                    }
                    modal.modal('toggle');
                });
        
                $('#{$this->modalID}').modal('show');
                e.preventDefault();
            });
     
            // remove item
            grid.on('click', '.grid-row-remove', function () {
                selected = null;
                $(this).closest('.selectable-item').remove();
                $("{$this->getElementClassSelector()}").val(null);

                container.append(emptyElement);
            });
        
            var load = function (url) {
                $.get(url, function (data) {
                    modal.find('.modal-body').html(data);
                    modal.find('.select').iCheck({
                        radioClass:'iradio_minimal-blue',
                        checkboxClass:'icheckbox_minimal-blue'
                    });
                    modal.find('.box-header:first').hide();
        
                    modal.find('input.select').each(function (index, el) {
                        if ($(el).val() == selected) {
                            $(el).iCheck('toggle');
                        }
                    });
                });
            };

            grid.find('a[data-form="modal"]').on('modelCreated', (e) => {
                    
                var createdModelId = $(e.target).data('model-id');
                
                var input = $('.belongstomany-{$this->column()}').closest('.form-group').find('select.{$this->column()}');
             
                input
                .select2({data: [createdModelId]})
                .val([createdModelId])
                .trigger('change')
                .next()
                .addClass('hide');
                    
                container.html('');
        
                $.get("{$this->getLoadUrl()}&id=" + createdModelId, function(response){
                    var item = $(response).find('.selectable-item:first');
                    item.find('.column-__modal_selector__').remove();
                    item.find('.grid-row-remove').removeClass('hide');
                    container.append(item);
                });
        
            });
        
            
        })();
        SCRIPT;

        Admin::script($script);

        return $this;
    }

    protected function getOptions()
    {
        $options = [];

        if ($value = $this->value()) {
            $options = [$value => $value];
        }

        return $options;
    }
}
