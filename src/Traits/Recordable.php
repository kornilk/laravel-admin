<?php

namespace Encore\Admin\Traits;

use Altek\Accountant\Recordable as AltekRecordable;
use Altek\Accountant\Resolve;
use Altek\Accountant\Contracts\Identifiable;

trait Recordable
{
    use AltekRecordable {
        gather as public parentGather;
    }

    protected $excludeColumns = [];

    public function gather(string $event): array
    {
        $return = $this->parentGather($event);
        $user = Resolve::user();

        $return['original'] = $this->getOriginal();

        $return['properties'] = \Arr::except($return['properties'], array_merge(config('accountant.excludeColumns'), $this->excludeColumns));
        $return['original'] = \Arr::except($return['original'], array_merge(config('accountant.excludeColumns'), $this->excludeColumns));
        $return['modified'] = \Arr::except($return['modified'], array_merge(config('accountant.excludeColumns'), $this->excludeColumns));

        $return['extra'] = $this->supplyExtraExtended($event, $return['properties'], $user, $this->getMorphClass(), $this->getIdentifier());
        
        return $return;
    }

    public function supplyExtraExtended(string $event, array $properties, ?Identifiable $user, $contentClass, $contentIdentifier): array
    {
        return [];
    }

}
