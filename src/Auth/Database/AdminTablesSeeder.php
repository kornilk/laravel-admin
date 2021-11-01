<?php

namespace Encore\Admin\Auth\Database;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // create a user.
        Administrator::truncate();
        Administrator::create([
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin'),
            'name'     => 'Administrator',
        ]);

        // create a role.
        Role::truncate();
        Role::create([
            'name' => 'Administrator',
            'slug' => 'administrator',
        ]);

        // add role to user.
        Administrator::first()->roles()->save(Role::first());

        //create a permission
        Permission::truncate();
        Permission::insert([
            [
                'name'        => 'All permission',
                'slug'        => '*',
                'http_method' => '',
                'http_path'   => '*',
            ],
            [
                'name'        => 'Dashboard',
                'slug'        => 'dashboard',
                'http_method' => 'GET',
                'http_path'   => '/',
            ],
            [
                'name'        => 'Login',
                'slug'        => 'auth.login',
                'http_method' => '',
                'http_path'   => "/auth/login\r\n/auth/logout",
            ],
            [
                'name'        => 'User setting',
                'slug'        => 'auth.setting',
                'http_method' => 'GET,PUT',
                'http_path'   => '/auth/setting',
            ],
            [
                'name'        => 'User management',
                'slug'        => 'auth.management',
                'http_method' => '',
                'http_path'   => "/system/users*",
            ],
            [
                'name'        => 'User management',
                'slug'        => 'auth.management',
                'http_method' => '',
                'http_path'   => "/system/users",
            ],
            [
                'name'        => 'Operation Log',
                'slug'        => 'system.operation-logs',
                'http_method' => '',
                'http_path'   => "system/operation-logs*",
            ],
        ]);

        Role::first()->permissions()->save(Permission::first());

        // add default menus.
        Menu::truncate();
        Menu::insert([
            [
                'parent_id' => 0,
                'order'     => 1,
                'title'     => 'Dashboard',
                'icon'      => 'fa-bar-chart',
                'uri'       => '/',
                'permission' => null,
            ],
            [
                'parent_id' => 0,
                'order'     => 2,
                'title'     => 'Settings',
                'icon'      => 'fa-tasks',
                'uri'       => '',
                'permission' => null,
            ],
            [
                'parent_id' => 2,
                'order'     => 3,
                'title'     => 'Administrators',
                'icon'      => 'fa-users',
                'uri'       => 'system/users',
                'permission' => 'auth.management',
            ],
            [
                'parent_id' => 2,
                'order'     => 4,
                'title'     => 'Roles',
                'icon'      => 'fa-user',
                'uri'       => 'system/roles',
                'permission' => null,
            ],
            [
                'parent_id' => 2,
                'order'     => 5,
                'title'     => 'Permissions',
                'icon'      => 'fa-ban',
                'uri'       => 'system/permissions',
                'permission' => null,
            ],
            [
                'parent_id' => 2,
                'order'     => 6,
                'title'     => 'Menu',
                'icon'      => 'fa-bars',
                'uri'       => 'system/menu',
                'permission' => null,
            ],
            [
                'parent_id' => 2,
                'order'     => 7,
                'title'     => 'Operation log',
                'icon'      => 'fa-history',
                'uri'       => 'system/operation-logs',
                'permission' => 'system.operation-logs',
            ],
            [
                'parent_id' => 2,
                'order'     => 8,
                'title'     => 'System log',
                'icon'      => 'fa-database',
                'uri'       => 'system/system-logs',
                'permission' => null,
            ],
            [
                'parent_id' => 2,
                'order'     => 9,
                'title'     => 'Terminal',
                'icon'      => 'fa-terminal',
                'uri'       => 'system/artisan',
                'permission' => null,
            ],
        ]);

        // add role to menu.
        Menu::find(2)->roles()->save(Role::first());

        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
