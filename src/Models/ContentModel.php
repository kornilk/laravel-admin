<?php

namespace Encore\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Altek\Accountant\Contracts\Recordable;

class ContentModel extends Model implements Recordable
{
    use SoftDeletes;
    use \Altek\Eventually\Eventually;
    use \Altek\Accountant\Recordable;

}