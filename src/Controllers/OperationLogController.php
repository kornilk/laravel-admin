<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Facades\OperationLog;
use Encore\Admin\Models\Ledger;

class OperationLogController extends AdminController
{
    protected $title = '';
    protected $model = Ledger::class;

    public function __construct()
    {
        parent::__construct();
        $this->title = __('admin.Operation log');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return OperationLog::grid();
    }

    

}
