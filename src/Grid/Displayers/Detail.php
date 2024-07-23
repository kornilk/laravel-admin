<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;
use Str;

class Detail extends AbstractDisplayer
{
    public function display($icon = false, $href = null, $limit = 90)
    {
        if (empty($href)) $href = $this->getResource() . '/' . $this->row->id;
        $icon = $icon ? '<i class="fa fa-eye mr-2" aria-hidden="true"></i>' : '';
        return '<a title="' . $this->value . '" href="' . $href . '">' . $icon . Str::limit($this->value, $limit, '...') . '</a>';
    }
}
