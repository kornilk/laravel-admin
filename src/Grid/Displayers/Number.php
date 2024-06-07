<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;
use Str;

class Number extends AbstractDisplayer
{
    public function display($decimals = 0, $decimal_separator = ',', $thousands_separator = ' ')
    {
        return is_numeric($this->value) ? number_format($this->value, $decimals, $decimal_separator, $thousands_separator) : $this->value;
    }
}
