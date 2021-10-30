<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Admin;

class BelongsToMany extends MultipleSelect
{
    use BelongsToRelation;

    protected function addScript()
    {
        $formClass = $this->form->getFormClass();

        $script = <<<SCRIPT
;(function () {

    var grid = $('.{$formClass} .belongstomany-{$this->column()}');
    var modal = $('#{$this->modalID}');
    var container = grid.find('.selectable-container');
    var selected = $("{$this->getElementClassSelector()}").val() || [];
    var items = {};
    var emptyElement = $(grid.find('template.empty').html());

    if (container.prop('nodeName') !== 'TABLE') {
        emptyElement = emptyElement.find('.empty-grid');
    }

    container.find('.selectable-item').each(function (index, item) {
        if ($(item).find('.grid-row-remove').length > 0) {
            items[$(item).find('.grid-row-remove').data('key')] = $(item);
        }
    });

    // open modal
    grid.find('.select-relation').click(function (e) {
        $('#{$this->modalID}').modal('show');
        e.preventDefault();
    });

    // remove row
    grid.on('click', '.grid-row-remove', function () {
        val = $(this).data('key').toString();

        var index = selected.indexOf(val);
        if (index !== -1) {
           selected.splice(index, 1);
           delete items[val];
        }

        $(this).parents('.selectable-item').remove();
        $("{$this->getElementClassSelector()}").val(selected);

        if (selected.length == 0) {
            container.append(emptyElement);
        }
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
                if ($.inArray($(el).val().toString(), selected) >=0 ) {
                    $(el).iCheck('toggle');
                }
            });
        });
    };

    var update = function (callback) {

        $("{$this->getElementClassSelector()}")
            .select2({data: selected})
            .val(selected)
            .trigger('change')
            .next()
            .addClass('hide');

            container.empty();

        Object.values(items).forEach(function (item) {
            item.find('.grid-row-remove').removeClass('hide');
            item.find('.column-__modal_selector__').remove();
            container.append(item);

            item.find('.grid-row-remove').removeClass('hide');
            container.append(item);
        });

        callback();
    };

    modal.on('show.bs.modal', function (e) {
        load("{$this->getLoadUrl(1)}");
    }).on('click', '.page-item a, .filter-box a', function (e) {
        load($(this).attr('href'));
        e.preventDefault();
    }).on('click', '.selectable-item', function (e) {
        $(this).find('input.select').iCheck('toggle');
        e.preventDefault();
    }).on('submit', '.box-header form', function (e) {
        load($(this).attr('action')+'&'+$(this).serialize());
        e.preventDefault();
        return false;
    }).on('ifChecked', 'input.select', function (e) {
        if (selected.indexOf($(this).val()) < 0) {
            selected.push($(this).val());
            items[$(e.target).val()] = $(e.target).closest('.selectable-item');
        }
    }).on('ifUnchecked', 'input.select', function (e) {
           var val = $(this).val();
           var index = selected.indexOf(val);
           if (index !== -1) {
               selected.splice(index, 1);
               delete items[$(e.target).val()];
           }
    }).find('.modal-footer .submit').click(function () {
        update(function () {
            modal.modal('toggle');
        });
    });

    grid.find('a[data-form="modal"]').on('modelCreated', (e) => {
                    
        var createdModelId = $(e.target).data('model-id');
        
        var input = $('.belongstomany-{$this->column()}').closest('.form-group').find('select.{$this->column()}');
        var selected = input.val();
    
        if (typeof selected !== 'object' || selected === null ) selected = [];
        selected.push(createdModelId);
    
        input
        .select2({data: selected})
        .val(selected)
        .trigger('change')
        .next()
        .addClass('hide');
            
        container.find('.empty-grid').remove();

        $.get("{$this->getLoadUrl(1)}&id=" + createdModelId, function(response){
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

        if ($this->value()) {
            $options = array_combine($this->value(), $this->value());
        }

        return $options;
    }
}
