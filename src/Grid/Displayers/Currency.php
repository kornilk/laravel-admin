<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class Currency extends AbstractDisplayer
{
    public function display($affix = ' Ft', $prefix="", $decimals = 0, $decimal_separator = ',', $thousands_separator = ' ')
    {
        $value = is_numeric($this->value) ? number_format($this->value, $decimals, $decimal_separator, $thousands_separator) : $this->value;
        return "{$prefix}{$value}{$affix}";
    }
}
