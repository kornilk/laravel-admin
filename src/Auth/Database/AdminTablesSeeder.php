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
                'http_path'   => "/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs",
            ],
            [
                'name'        => 'Logs',
                'slug'        => 'ext.log-viewer',
                'http_method' => '',
                'http_path'   => "logs*",
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
                'uri'       => 'auth/users',
                'permission' => 'auth.management',
            ],
            [
                'parent_id' => 2,
                'order'     => 4,
                'title'     => 'Roles',
                'icon'      => 'fa-user',
                'uri'       => 'auth/roles',
                'permission' => null,
            ],
            [
                'parent_id' => 2,
                'order'     => 5,
                'title'     => 'Permissions',
                'icon'      => 'fa-ban',
                'uri'       => 'auth/permissions',
                'permission' => null,
            ],
            [
                'parent_id' => 2,
                'order'     => 6,
                'title'     => 'Menu',
                'icon'      => 'fa-bars',
                'uri'       => 'auth/menu',
                'permission' => null,
            ],
            [
                'parent_id' => 2,
                'order'     => 7,
                'title'     => 'Operation log',
                'icon'      => 'fa-history',
                'uri'       => 'auth/logs',
                'permission' => null,
            ],
            [
                'parent_id' => 2,
                'order'     => 8,
                'title'     => 'System log',
                'icon'      => 'fa-database',
                'uri'       => 'logs',
                'permission' => null,
            ],
        ]);

        // add role to menu.
        Menu::find(2)->roles()->save(Role::first());

        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
