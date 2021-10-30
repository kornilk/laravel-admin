<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;
use Str;

class Edit extends AbstractDisplayer
{
    public function display($editIcon = false, $limit = 90)
    {
        if ($editIcon) {
            return '<a title="' . $this->value . '" href="' . $this->getResource() . '/' . $this->row->id . '">' . Str::limit($this->value, $limit, '...') . '</a><a style="padding:0 10px;" href="' . $this->getResource() . '/' . $this->row->id . '/edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
        }
        return '<a title="' . $this->value . '" href="' . $this->getResource() . '/' . $this->row->id . '/edit">' . Str::limit($this->value, $limit, '...') . '</a>';
    }
}
