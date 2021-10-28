<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class StripTags extends AbstractDisplayer
{
    public function display(){
        return strip_tags($this->value);
    }
}
