<?php

namespace Encore\Admin\Traits;

trait PermissionTrait
{
    private $separators = [
        'http_method' => ',',
        'http_path' => "\n",
    ];

    protected function updatePermission(string $slug, array $add = [], array $update = [])
    {
        $permission = \DB::table('admin_permissions')->where('slug', $slug)->first();

        foreach ($add as $key => $value){
            $newValue = '';
            $separator = isset($this->separators[$key]) ? $this->separators[$key] : ' ';
            
            if ($permission) $newValue = $permission->{$key};
            $newValue .= (empty($method) ? '' : $separator) . $value;

            \DB::table('admin_permissions')->where('slug', $slug)->update([$key => $newValue]);
        }

        foreach ($update as $key => $value){

            \DB::table('admin_permissions')->where('slug', $slug)->update([$key => $value]);
        }
      
    }

    protected function createPermission(array $data)
    {
        return  \DB::table('admin_permissions')->admin_roles($data);
    }

    protected function createRole(string $name, string $slug)
    {
        return \DB::table('admin_roles')->insertGetId([
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    protected function removeRole(string $slug)
    {
        $role = \DB::table('admin_roles')->where('slug', $slug)->first();

        if ($role) {
            \DB::table('admin_role_permissions')->where('role_id', $role->id)->delete();
        }

        \DB::table('admin_roles')->where('slug', $slug)->delete();

        return $this;
    }

    protected function addRoleToMenu(string $menuUri, string $roleSlug)
    {
        $menu = \DB::table('admin_menu')->where('uri', $menuUri)->first();
        $role = \DB::table('admin_roles')->where('slug', $roleSlug)->first();

        if ($menu && $role){
            \DB::table('admin_role_menu')->insert([
                'role_id' => $role->id,
                'menu_id' => $menu->id,
            ]);
        }

        return $this;
    }

    protected function createRoleByPermissionSlug(string $name, string $slug, string $permissionSlug)
    {
        $permissions = \DB::table('admin_permissions')->where('slug', 'like', $permissionSlug)->get();

        $roleId = $this->createRole($name, $slug);
     
        foreach ($permissions as $permission){
            \DB::table('admin_role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permission->id,
            ]);
        }

        return $this;
    }

    protected function addContentPermissions(string $slug, string $name, bool $publish = FALSE)
    {

        \DB::table('admin_permissions')->insert([
            'name' => '{' . $name . '} - {view}',
            'slug' => '' . $slug . '.show',
            'http_method' => 'GET,HEAD',
            'http_path' => '/' . $slug . '/*',
        ]);

        \DB::table('admin_permissions')->insert([
            'name' => '{' . $name . '} - {edit}',
            'slug' => '' . $slug . '.edit',
            'http_method' => 'PUT,HEAD,GET',
            'http_path' => '/' . $slug . '/*',
        ]);

        \DB::table('admin_permissions')->insert([
            'name' => '{' . $name . '} - {create}',
            'slug' => '' . $slug . '.create',
            'http_method' => 'GET,HEAD,POST',
            'http_path' => '/' . $slug . '/create' . "\n" . '/' . $slug,
        ]);

        \DB::table('admin_permissions')->insert([
            'name' => '{' . $name . '} - {delete}',
            'slug' => '' . $slug . '.destroy',
            'http_method' => 'DELETE',
            'http_path' => '/' . $slug . '/*',
        ]);

        if ($publish) {

            \DB::table('admin_permissions')->insert([
                'name' => '{' . $name . '} - {publish}',
                'slug' => '' . $slug . '.publish',
                'http_method' => 'POST,PUT,PATCH',
                'http_path' => '/' . $slug . '/publish',
            ]);

        }

        return $this;

    }

    protected function removeContentPermissions(string $slug)
    {
        $this->removePermission('' . $slug . '.show');
        $this->removePermission('' . $slug . '.create');
        $this->removePermission('' . $slug . '.edit');
        $this->removePermission('' . $slug . '.destroy');
        $this->removePermission('' . $slug . '.publish');

        return $this;
    }

    protected function removePermission(string $slug)
    {
        $permission = \DB::table('admin_permissions')->where('slug', $slug)->first();

        if ($permission) {
            \DB::table('admin_role_permissions')->where('permission_id', $permission->id)->delete();
        }

        \DB::table('admin_permissions')->where('slug', $slug)->delete();

        return $this;
    }
}
