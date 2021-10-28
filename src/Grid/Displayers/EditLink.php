<?php

namespace App\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;
use Str;

class EditLink extends AbstractDisplayer
{
    public function display($limit = 90){

        return '<a title="'.$this->value.'" href="'.$this->getResource().'/'.$this->row->id.'/edit">'. Str::limit($this->value, $limit, '...') .'</a>';
    }
}
