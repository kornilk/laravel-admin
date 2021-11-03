<?php

namespace Encore\Admin\Auth;

use Encore\Admin\Auth\Database\Permission as DatabasePermission;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Middleware\Pjax;
use Illuminate\Http\Request;

class Permission
{
    protected static $methodPermission = [
        'index' => 'show',
        'show' => 'show',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'edit',
        'update' => 'edit',
        'destroy' => 'destroy',
    ];

    public static function hasAccessBySlug($slug = null, $method = null){
        if (empty($method)) $method = \Route::current()->getActionMethod();
        if (empty($slug)) $slug = str_replace(config('admin.route.prefix') . '/', '', \Route::current()->uri);

        $slug = str_replace('/', '.', $slug);

        $permission = "{$slug}." . (isset(static::$methodPermission[$method]) ? static::$methodPermission[$method] : $method);

        if (DatabasePermission::isPermission($permission)) return \Admin::user()->can($permission);

        return true;
    }

    public static function hasAccessByPath($path = null){

        if (empty($path)) $path = \Route::current()->uri;
        $path = '/' . ltrim(parse_url($path, PHP_URL_PATH),"/");

        $adminPrefix = config('admin.route.prefix');
    
        if (!empty($adminPrefix)) {
            $path = ltrim($path, "/{$adminPrefix}");
            $path = $adminPrefix . '/' . ltrim(parse_url($path, PHP_URL_PATH),"/");
        }

        foreach (\Admin::user()->allPermissions() as $permission){
            if ($permission->shouldPassThroughPath($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check permission.
     *
     * @param $permission
     *
     * @return true
     */
    public static function check($permission)
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (is_array($permission)) {
            collect($permission)->each(function ($permission) {
                call_user_func([self::class, 'check'], $permission);
            });

            return;
        }

        if (Admin::user()->cannot($permission)) {
            static::error();
        }
    }

    /**
     * Roles allowed to access.
     *
     * @param $roles
     *
     * @return true
     */
    public static function allow($roles)
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (!Admin::user()->inRoles($roles)) {
            static::error();
        }
    }

    /**
     * Don't check permission.
     *
     * @return bool
     */
    public static function free()
    {
        return true;
    }

    /**
     * Roles denied to access.
     *
     * @param $roles
     *
     * @return true
     */
    public static function deny($roles)
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (Admin::user()->inRoles($roles)) {
            static::error();
        }
    }

    /**
     * Send error response page.
     */
    public static function error()
    {
        $response = response(Admin::content()->withError(trans('admin.deny')));

        if (!request()->pjax() && request()->ajax()) {
            abort(403, trans('admin.deny'));
        }

        Pjax::respond($response);
    }

    /**
     * If current user is administrator.
     *
     * @return mixed
     */
    public static function isAdministrator()
    {
        return Admin::user()->isRole('administrator');
    }
}
