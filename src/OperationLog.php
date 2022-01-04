<?php

namespace Encore\Admin;

use Encore\Admin\Models\Ledger;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;

class OperationLog
{
    public function grid($user_type = null, $user_id = null){
        $grid = new Grid(new Ledger());
        $grid->model()->orderBy('id', 'desc');

        if ($user_type) $grid->model()->where('user_type', $user_type);
        if ($user_id) $grid->model()->where('user_id', $user_id);

        $grid->column('user', __('admin.User'))->display(function(){
            $userClass = $this->user_type;
            $userId = $this->user_id;
            if (empty($userClass) || empty($userId)) return $this->extra['userReadebleIdentifier'];
            $user = $userClass::where('id', $userId)->first();
            return $user ? $user->contentReadableIdentifier : $this->extra['userReadebleIdentifier'];
        });

        $grid->column('content', __('admin.content'))->display(function(){
            $typeClass = $this->recordable_type;
            if (empty($typeClass) || !method_exists($typeClass, 'getContentTitle')) return '';
            return __($typeClass::getContentTitle());
        });

        $grid->column('recordable_type', __('admin.Identifier'))->display(function($value, $column){
            $typeClass = $this->recordable_type;
            $typeId = $this->recordable_id;
            if (empty($typeClass) || empty($typeId)) return '';
            $content = $typeClass::where('id', $typeId)->first();

            $value = \Str::limit($this->extra['contentReadebleIdentifier'], 30);

            if ($content && method_exists($typeClass, 'contentReadableIdentifier')) {
                $value = \Str::limit($content->contentReadableIdentifier, 30);
            }

            if ($content && method_exists($typeClass, 'getContentSlug')) {
                $url = $typeClass::getContentAdminRoute('show', [$typeId]);

                if (\Admin::permission()::hasAccessBySlug($typeClass::getContentSlug().'.show') && \Admin::permission()::hasAccessByPath($url)){
                    $value = '<a title="' . $value . '" href="' . $url . '"><i style="margin-right:5px;" class="fa fa-eye" aria-hidden="true"></i>' . $value . '</a>';
                }

                
            }
            return $value;
        });

        $that = $this;

        $grid->column('event', __('admin.event'))->display(function($value){
            $named = [
                'created',
                'updated',
                'restored',
                'deleted',
                'forceDeleted',
            ];
            return in_array($value, $named) ? __("admin.{$value}") : __("admin.updated");
        })->modal(__('admin.Event datas'), function ($model) use($that) {

            $new_values = $model->properties;
            $old_values = $model->original;

            $new_table = !empty($new_values) ? $that->getFieldTable($new_values, $model) : null;
            $old_table = !empty($old_values) ? $that->getFieldTable($old_values, $model) : null;

            if (!empty($new_table) || !empty($old_table)){
                $tab = new Tab();

                if (!empty($new_table) && $new_table instanceof Table) $tab->add(__('admin.New datas'), $new_table);
                if (!empty($old_table) && $old_table instanceof Table) $tab->add(__('admin.Old datas'), $old_table);

                return $tab->render();
            }

            return '<div>'.__('admin.No additional information available.').'</div>';
        })->dot([
            'created' => 'info',
            'updated' => 'success',
            'restored' => 'primary',
            'deleted' => 'danger',
            'forceDeleted' => 'danger',
            'synced' => 'success',
            'existingPivotUpdated' => 'success',
            'attached' => 'success',
            'detached' => 'success',
            'toggled' => 'success',
        ]);

        $grid->column('created_at', __('admin.created_at_date_time'));
     
        $grid->filter(function ($filter) {

            $filter->where(function ($query) {

                $query->where('properties', 'like', "%{$this->input}%")
                    ->orWhere('modified', 'like', "%{$this->input}%")
                    ->orWhere('pivot', 'like', "%{$this->input}%")
                    ->orWhere('extra', 'like', "%{$this->input}%");
            }, __('admin.Search'));
        });

        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->disableActions();
        $grid->disableColumnSelector();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });

        $grid->disableCreateButton();

        return $grid;
    }

    public function getFieldTable($values, $item){
        $data = [];
        $typeClass = $item->recordable_type;
        $pivot = $item->pivot;
        if (empty($typeClass) || !method_exists($typeClass, 'getContentTitle')) return null;

        if (!empty($pivot)){
            $labels = [];

            foreach ($values as $key => $value) {
                $labels[] = method_exists($typeClass, "getRecordablePivot{$pivot['relation']}Value") ? $typeClass::{"getRecordablePivot{$pivot['relation']}Value"}($value) : '<span class="label label-success">'.$value['readableIdentifier'].'</span>';
            }

            $data[] = [
                __($pivot['related']::getContentTitle()),
                implode('<br>', $labels),
            ];
        } else {
            foreach ($values as $key => $value) {
                $data[] = [$typeClass::label($key), $typeClass::getReadableValue($key, $value)];
            }
        }


        return new Table([__('admin.Field name'), __('admin.Data')], $data);
    }
}