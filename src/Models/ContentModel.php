<?php

namespace Encore\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentModel extends Model implements Auditable
{
    use SoftDeletes;
    use \Altek\Eventually\Eventually;
    use \Altek\Accountant\Recordable;

}