<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Altek\Accountant\Models\Ladger;

class OperationLogController extends AdminController
{
    protected $title = '';
    protected $model = Ladger::class;

    public function __construct()
    {
        parent::__construct();
        $this->title = __('admin.Operation Log');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new $this->model());

        $grid->column('value', $this->model::label('value'))->stripTags()->edit();
        $grid->column('context', $this->model::label('context'));
        $grid->column('placeholder', $this->model::label('placeholder'));
       
        $grid->filter(function($filter){

            $filter->disableIdFilter();

            $filter->where(function ($query) {

                $query->where('value', 'like', "%{$this->input}%")
                    ->orWhere('context', 'like', "%{$this->input}%")
                    ->orWhere('placeholder', 'like', "%{$this->input}%");
            
            }, __('admin.Search'));

        
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });

        $grid->disableCreateButton();

        return $grid;
    }

}
