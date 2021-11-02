<?php

declare(strict_types=1);

namespace Encore\Admin\Drivers;

use Altek\Accountant\Contracts\Ledger;
use Altek\Accountant\Contracts\Notary;
use Altek\Accountant\Contracts\Recordable;
use Altek\Accountant\Drivers\Database;
use Altek\Accountant\Exceptions\AccountantException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class AccountantDatabase extends Database
{
    /**
     * {@inheritdoc}
     */
    public function record(
        Recordable $model,
        string $event,
        string $pivotRelation = null,
        array $pivotProperties = []
    ): Ledger {
        $notary = Config::get('accountant.notary');

        if (! \is_subclass_of($notary, Notary::class)) {
            throw new AccountantException(\sprintf('Invalid Notary implementation: "%s"', $notary));
        }

        $implementation = Config::get('accountant.ledger.implementation');

        if (! \is_subclass_of($implementation, Ledger::class)) {
            throw new AccountantException(\sprintf('Invalid Ledger implementation: "%s"', $implementation));
        }

        $ledger = new $implementation();

        $data = $model->gather($event);
        $related = null;

        if ($pivotRelation){

            $original = new Collection();
            $deletedItems = new Collection();
            $newItems = new Collection();
            $modifiedItems = new Collection();
            $properties = new Collection();
            $relation = $model->{$pivotRelation}();
            $relatedPivotKeyName = $relation->getRelatedPivotKeyName();
            $related = $relation->getRelated();
            //$relation->allRelatedIds()

            foreach ($model->{$pivotRelation} as $item){

                $original->push([
                    'id' => $item->id,
                    'pivot' => $item->pivot->getAttributes(),
                    'readableIdentifier' => $item->contentReadableIdentifier,
                ]);
            }

            $data['original'] = $original->toArray();

            $modified = [];
            $originalByKey = $original->keyBy('id');

            foreach ($pivotProperties as $item){

                $id = $item[$relatedPivotKeyName];

                $relatedItem = $related->where('id', $id)->first();

                $value = [
                    'id' => $id,
                    'pivot' => $item,
                    'readableIdentifier' => $relatedItem ? $relatedItem->contentReadableIdentifier : '',
                ];

                if (!isset($originalByKey[$id])){
                    $newItems->push($value);
                } else if ($originalByKey[$id]['pivot'] != $value['pivot']) {
                    $modifiedItems->push($value);
                }

                $properties->push($value);

            }

            $propertiesByKey = $properties->keyBy('id');

            foreach ($data['original'] as $item){
                if (!isset($propertiesByKey[$item['id']])) $deletedItems->push($item);
            }

            if (!$newItems->isEmpty()) $modified['new'] = $newItems->toArray();
            if (!$modifiedItems->isEmpty()) $modified['modified'] = $modifiedItems->toArray();
            if (!$deletedItems->isEmpty()) $modified['deleted'] = $deletedItems->toArray();

            $data['properties'] = $properties->toArray();
            $data['modified'] = $modified;

        }

        if (in_array($data['event'], ['restored', 'deleted', 'forceDeleted'])){
            $data['properties'] = [];
            $data['modified'] = [];
            $data['original'] = [];
        }

        // Set the Ledger properties
        foreach ($data as $property => $value) {
            $ledger->setAttribute($property, $value);
        }

        if ($ledger->usesTimestamps()) {
            $ledger->setCreatedAt($ledger->freshTimestamp())
                ->setUpdatedAt($ledger->freshTimestamp());
        }

        $ledger->setAttribute('pivot', $pivotRelation ? [
            'relation'   => $pivotRelation,
            'properties' => $pivotProperties,
            'related' => get_class($related),
        ] : []);

        // Sign and store the record
        $ledger->setAttribute('signature', \call_user_func([$notary, 'sign'], $ledger->attributesToArray()));

        if (!empty($data['modified']) || in_array($data['event'], ['restored', 'deleted', 'forceDeleted'])) $ledger->save();

        return $ledger;
    }
}
