<?php

namespace Encore\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Encore\Admin\Contracts\Recordable;

class ContentModel extends Model implements Recordable
{
    use SoftDeletes;
    use \Altek\Eventually\Eventually;
    use \Encore\Admin\Traits\Recordable;

    public function supplyExtraExtended(string $event, array $properties, $user, $contentClass, $contentIdentifier): array
    {
        return [
            'contentReadebleIdentifier' => $properties[$contentClass::getContentBaseColumn()],
            'userReadebleIdentifier' => $user->contentReadableIdentifier,
        ];
    }
}