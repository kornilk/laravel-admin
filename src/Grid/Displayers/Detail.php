<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;
use Str;

class Detail extends AbstractDisplayer
{
    public function display($href = null, $limit = 90)
    {
        if (empty($href)) $href = $this->getResource() . '/' . $this->row->id;
        return '<a title="' . $this->value . '" href="' . $href . '"><i class="fa fa-eye" aria-hidden="true"></i>' . Str::limit($this->value, $limit, '...') . '</a>';
    }
}
