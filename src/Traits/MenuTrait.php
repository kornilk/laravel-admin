<?php

namespace Encore\Admin\Traits;

Trait MenuTrait
{
    protected function createMenu(array $data){
        $order = NULL;

        if (!isset($data['order'])) {

			if (!empty($data['after'])) {

                $query = \DB::table('admin_menu');
                
                if (is_string($data['after'])) {
                    $query::where('uri', $data['after']);
                } else {
                    $query::where('order', $data['after']);
                }

                $item = $query->first();

				if ($item) $order = $item->order + 1;

			}

			if (is_null($order)) {
                $item = \DB::table('admin_menu')
                ->orderBy('order', 'desc')->first();
				if ($item) $order = $item->order + 1;

				if (is_null($order)) $order = 1;
			}

		}

        if (!is_null($order)) $data['order'] = $order;
        if (isset($data['after'])) unset($data['after']);
        
        \DB::table('admin_menu')->insert($data);
    }

    protected function removeMenu(string $uri){

        \DB::table('admin_menu')->where('uri', $uri)->delete();
    }
}
