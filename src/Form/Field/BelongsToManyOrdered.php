<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Admin;
use Encore\Admin\Form\Field\BelongsToRelation;
use Encore\Admin\Form\Field\MultipleSelect;

class BelongsToManyOrdered extends MultipleSelect
{
    public $view = 'admin::form.belongstomany';

    use BelongsToRelation;

    protected function addScript()
    {
        $script = <<<SCRIPT
;(function () {

    var grid = $('.belongstomany-{$this->column()}');
    var modal = $('#{$this->modalID}');
    var table = grid.find('.grid-table');
    var selected = $("{$this->getElementClassSelector()}").val() || [];
    var rows = [];

    for (var index in selected){
        rows[index] = table.find('tbody tr[data-key="'+selected[index]+'"]');
    }
    
    table.find('tbody').empty();
    Object.values(rows).forEach(function (row) {
        table.find('tbody').append(row);
    });
    rows = [];

    Sortable.create(table.find('tbody')[0], {
        animation: 150,
        ghostClass: 'sortable-background',
        onSort: function (evt) {
            
            selected = [];
            table.find('tbody').children().each(function (index, tr) {
                selected.push($(this).data('key'));
            });

            rows = [];
            table.find('tbody').children().each(function (index, tr) {
                if ($(tr).find('.grid-row-remove').length > 0) {
                    rows['n'+$(tr).find('.grid-row-remove').data('key')] = $(tr);
                }
            });

            update(function(){});
        }
    })

    table.find('tbody').children().each(function (index, tr) {
        if ($(tr).find('.grid-row-remove').length > 0) {
            rows['n'+$(tr).find('.grid-row-remove').data('key')] = $(tr);
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
           delete rows['n'+val];
        }

        $(this).parents('tr').remove();
        $("{$this->getElementClassSelector()}").val(selected);

        if (selected.length == 0) {
            var empty = $('.belongstomany-{$this->column()}').find('template.empty').html();
            table.find('tbody').append(empty);
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

        table.find('tbody').empty();

        Object.values(rows).forEach(function (row) {
            row.find('td:last a').removeClass('hide');
            row.find('td.column-__modal_selector__').remove();
            table.find('tbody').append(row);
        });

        if (selected.length == 0) {
            var empty = $('.belongstomany-{$this->column()}').find('template.empty').html();
            table.find('tbody').append(empty);
        } else {
            table.find('.empty-grid').parent().remove();
        }

        callback();
    };

    modal.on('show.bs.modal', function (e) {
        var val = $("{$this->getElementClassSelector()}").select2().val();
        for (var i in val){
            if (selected.indexOf(val[i]) < 0) {
                selected.push(val[i]);
                rows['n'+val[i]] = table.find('tbody tr[data-key="'+val[i]+'"]');
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
    }).on('click', 'tr', function (e) {
        $(this).find('input.select').iCheck('toggle');
        e.preventDefault();
    }).on('submit', '.box-header form', function (e) {
        load($(this).attr('action')+'&'+$(this).serialize());
        e.preventDefault();
        return false;
    }).on('ifChecked', 'input.select', function (e) {
        if (selected.indexOf($(this).val()) < 0) {
            selected.push($(this).val());
            rows['n'+$(e.target).val()] = $(e.target).parents('tr');
        }
    }).on('ifUnchecked', 'input.select', function (e) {
           var val = $(this).val();
           var index = selected.indexOf(val);
           if (index !== -1) {
               selected.splice(index, 1);
               delete rows['n'+$(e.target).val()];
           }
    }).find('.modal-footer .submit').click(function () {
        update(function () {
            modal.modal('toggle');
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

    public function prepare($value)
    {
        if (is_array($value)){
            $newValue = [];
            foreach ($value as $index => $current){
                if (!is_numeric($current)) continue;
                $newValue[$current] = ['order' => $index];
            }
            return $newValue;
        }
        return $value;
    }
}
