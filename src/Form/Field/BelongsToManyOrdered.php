<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Admin;
use Encore\Admin\Form\Field\BelongsToRelation;
use Encore\Admin\Form\Field\MultipleSelect;

class BelongsToManyOrdered extends MultipleSelect
{
    public $view = 'admin::form.belongstomany';

    use BelongsToRelation;

    protected static $js = [
        '/vendor/laravel-admin/AdminLTE/plugins/select2/select2.full.min.js',
        '/vendor/laravel-admin/sortable/sortable.umd.js',
    ];

    protected function addScript()
    {
        $formClass = $this->form->getFormClass();

        $script = <<<SCRIPT
;(function () {

    var grid = $('.{$formClass} .belongstomany-{$this->column()}');
    var modal = $('#{$this->modalID}');
    var container = grid.find('.selectable-container');
    var selected = $("{$this->getElementClassSelector()}").val() || [];

    var items = [];
    var emptyElement = $(grid.find('template.empty').html());

    if (container.prop('nodeName') !== 'TABLE') {
        emptyElement = emptyElement.find('.empty-grid');
    }

    for (var index in selected){
        items[index] = container.find('.selectable-item[data-key="'+selected[index]+'"]');
    }
    
    container.empty();
    Object.values(items).forEach(function (item) {
        container.append(item);
    });

    if (items.length === 0) container.append(emptyElement);

    items = [];

    Sortable.create(container[0], {
        animation: 150,
        ghostClass: 'sortable-background',
        onSort: function (evt) {
            
            selected = [];
            container.find('.selectable-item').each(function (index, item) {
                selected.push($(this).data('key') + '');
            });

            items = [];
            container.find('.selectable-item').each(function (index, item) {
                //if ($(item).find('.grid-row-remove').length > 0) {
                    items['n'+$(item).find('.grid-row-remove').data('key')] = $(item);
                //}
            });
  
            update(function(){});
        }
    })

    container.find('.selectable-item').each(function (index, item) {
        if ($(item).find('.grid-row-remove').length > 0) {
            items['n'+$(item).find('.grid-row-remove').data('key')] = $(item);
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
           delete items['n'+val];
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
            modal.find('.filter-box .icheck').iCheck({
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
            .select2().find('option').remove();

        for (var i in selected){
            $("{$this->getElementClassSelector()}").select2().append('<option value="'+selected[i]+'">'+selected[i]+'</option>');
        }

        $("{$this->getElementClassSelector()}")
            .select2({data: selected})
            .val(selected)
            .trigger('change')
            .next()
            .addClass('hide');

            container.empty();

        Object.values(items).forEach(function (item) {
            item.find('.grid-row-remove').removeClass('hide');
            item.find('.grid-row-edit').removeClass('hide');
            item.find('.column-__modal_selector__').remove();
            item.find('.grid-row-edit').on('formResponse', formResponse);
            container.append(item);
        });

        callback();
    };

    modal.on('show.bs.modal', function (e) {
        var val = $("{$this->getElementClassSelector()}").select2().val();
        for (var i in val){
            if (selected.indexOf(val[i]) < 0) {
                selected.push(val[i]);
                items['n'+val[i]] = container.find('.selectable-item[data-key="'+val[i]+'"]');
            }
        }
        load("{$this->getLoadUrl(1)}");
    }).on('hidden.bs.modal', function (e) {
        if ($('body .wrapper>.modal').length > 0) $('body').addClass('modal-open');
    }).on('hide.bs.modal', function(){
        $("{$this->getElementClassSelector()}").next().addClass('hide');
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
        var val = $(this).val();
        if (selected.indexOf(val) < 0) {
            selected.push(val);
            items['n'+$(e.target).val()] = $(e.target).closest('.selectable-item');
        }
    }).on('ifUnchecked', 'input.select', function (e) {
           var val = $(this).val();
           var index = selected.indexOf(val);
           if (index !== -1) {
               selected.splice(index, 1);
               delete items['n'+$(e.target).val()];
           }
    }).find('.modal-footer .submit').click(function () {
        update(function () {
            modal.modal('toggle');
        });
    });

    var formResponse = function(e){
        var itemId = $(e.target).data('model-id');

        if (!(itemId instanceof Array)){
            itemId = [itemId];
        }
         
        if (typeof selected !== 'object' || selected === null ) selected = [];

        for (var i = 0; i < itemId.length; i++) {
            var isUpdated = selected.includes(itemId[i]) || selected.includes(itemId[i] + '');

            if (!isUpdated){
                selected.push(itemId[i] + '');

                $("{$this->getElementClassSelector()}")
                    .select2({data: selected})
                    .val(selected)
                    .trigger('change')
                    .next()
                    .addClass('hide');
            }
                
            container.find('.empty-grid').remove();

            $.get("{$this->getLoadUrl()}&id=" + itemId[i], function(response){
                
                var item = $(response).find('.selectable-item:first');
                item.find('.column-__modal_selector__').remove();
                item.find('.grid-row-remove').removeClass('hide');
                item.find('.grid-row-edit').removeClass('hide');
                item.attr('data-key', itemId[i]);
                item.find('.grid-row-edit').on('formResponse', formResponse);

                if (isUpdated) {
                    items[itemId[i]].replaceWith(item);
                    items[itemId[i]] = item;
                } else {
                    container.append(item);
                    items[itemId[i]] = item;
                }
            });
        }

    }

    grid.find('a[data-form="modal"]').on('formResponse', formResponse);
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

    public function prepare($value)
    {
        if (is_array($value)){
            $newValue = [];
            foreach ($value as $index => $current){
                if (!is_numeric($current)) continue;
                $newValue[$current] = ['weight' => $index];
            }
            return $newValue;
        }
        return $value;
    }
}
