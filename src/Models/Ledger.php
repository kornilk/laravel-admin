<?php

namespace Encore\Admin\Models;

use Altek\Accountant\Models\Ledger as AltekLedger;

class Ledger extends AltekLedger
{
    protected $casts = [
        'properties' => 'json',
        'original' => 'json',
        'modified'   => 'json',
        'pivot'      => 'json',
        'extra'      => 'json',
    ];
}