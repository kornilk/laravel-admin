<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Admin;

class BelongsTo extends Select
{
    use BelongsToRelation;

    protected function addScript()
    {
        $selectorPerfix = getSelectorFromForm($this->form);
        
        $script = <<<SCRIPT
        ;(function () {
        
            var grid = $('{$selectorPerfix}.belongsto-{$this->column()}');
            var modal = $('#{$this->modalID}');
        
            var table = grid.find('.grid-table');
            var selected = $("{$selectorPerfix}{$this->getElementClassSelector()}").val();
            var row = null;
        
            // open modal
            grid.find('.select-relation').click(function (e) {
        
                modal.off().on('show.bs.modal', function (e) {
                    load("{$this->getLoadUrl()}");
                }).on('hidden.bs.modal', function (e) {
                    if ($('body .wrapper>.modal').length > 0) $('body').addClass('modal-open');
                }).on('click', '.page-item a, .filter-box a', function (e) {
                    load($(this).attr('href'));
                    e.preventDefault();
                }).on('click', 'tr', function (e) {
                    $(this).find('input.select').iCheck('toggle');
                    e.preventDefault();
                }).on('submit', '.box-header form', function (e) {
                    load($(this).attr('action')+'&'+$(this).serialize());
                    return false;
                }).on('ifChecked', 'input.select', function (e) {
                    row = $(e.target).parents('tr');
                    selected = $(this).val();
                }).find('.modal-footer .submit').off().on('click', function () {
                    $("{$selectorPerfix}{$this->getElementClassSelector()}")
                        .select2({data: [selected]})
                        .val(selected)
                        .trigger('change')
                        .next()
                        .addClass('hide');
        
                    if (row) {
                        row.find('td:last a').removeClass('hide');
                        row.find('td:first').remove();
                        table.find('tbody').empty().append(row);
                    }
                    modal.modal('toggle');
                });
        
                $('#{$this->modalID}').modal('show');
                e.preventDefault();
            });
        
            // remove row
            grid.on('click', '.grid-row-remove', function () {
                selected = null;
                $(this).parents('tr').remove();
                $("{$this->getElementClassSelector()}").val(null);
        
                var empty = $('{$selectorPerfix}.belongsto-{$this->column()}').find('template.empty').html();
        
                table.find('tbody').append(empty);
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
