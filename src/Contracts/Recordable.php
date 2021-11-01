<?php

namespace Encore\Admin\Contracts;

use Altek\Accountant\Contracts\Identifiable;
use Altek\Accountant\Contracts\Recordable as AltekRecordable;

interface Recordable extends AltekRecordable
{
    public function supplyExtraExtended(string $event, array $properties, ?Identifiable $user, $contentClass, $contentIdentifier): array;
}