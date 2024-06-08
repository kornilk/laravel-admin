<?php

namespace Encore\Admin;

use Closure;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Controllers\AuthController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Traits\HasAssets;
use Encore\Admin\Widgets\Navbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

/**
 * Class Admin.
 */
class Admin
{
    use HasAssets;

    /**
     * The Laravel admin version.
     *
     * @var string
     */
    const VERSION = '1.8.17';

    /**
     * @var Navbar
     */
    protected $navbar;

    /**
     * @var array
     */
    protected $menu = [];

    /**
     * @var string
     */
    public static $metaTitle;

    /**
     * @var string
     */
    public static $favicon;

    /**
     * @var array
     */
    public static $extensions = [];

    /**
     * @var []Closure
     */
    protected static $bootingCallbacks = [];

    /**
     * @var []Closure
     */
    protected static $bootedCallbacks = [];

    /**
     * Returns the long version of Laravel-admin.
     *
     * @return string The long application version
     */
    public static function getLongVersion()
    {
        return sprintf('Laravel-admin <comment>version</comment> <info>%s</info>', self::VERSION);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \Encore\Admin\Grid
     *
     * @deprecated since v1.6.1
     */
    public function grid($model, Closure $callable)
    {
        return new Grid($this->getModel($model), $callable);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \Encore\Admin\Form
     *
     *  @deprecated since v1.6.1
     */
    public function form($model, Closure $callable)
    {
        return new Form($this->getModel($model), $callable);
    }

    /**
     * Build a tree.
     *
     * @param $model
     * @param Closure|null $callable
     *
     * @return \Encore\Admin\Tree
     */
    public function tree($model, Closure $callable = null)
    {
        return new Tree($this->getModel($model), $callable);
    }

    /**
     * Build show page.
     *
     * @param $model
     * @param mixed $callable
     *
     * @return Show
     *
     * @deprecated since v1.6.1
     */
    public function show($model, $callable = null)
    {
        return new Show($this->getModel($model), $callable);
    }

    /**
     * @param Closure $callable
     *
     * @return \Encore\Admin\Layout\Content
     *
     * @deprecated since v1.6.1
     */
    public function content(Closure $callable = null)
    {
        return new Content($callable);
    }

    /**
     * @param $model
     *
     * @return mixed
     */
    public function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (is_string($model) && class_exists($model)) {
            return $this->getModel(new $model());
        }

        throw new InvalidArgumentException("$model is not a valid model");
    }

    /**
     * Left sider-bar menu.
     *
     * @return array
     */
    public function menu()
    {
        if (!empty($this->menu)) {
            return $this->menu;
        }

        $menuClass = config('admin.database.menu_model');

        /** @var Menu $menuModel */
        $menuModel = new $menuClass();

        return $this->menu = $menuModel->toTree();
    }

    /**
     * @param array $menu
     *
     * @return array
     */
    public function menuLinks($menu = [])
    {
        if (empty($menu)) {
            $menu = $this->menu();
        }

        $links = [];

        foreach ($menu as $item) {
            if (!empty($item['children'])) {
                $links = array_merge($links, $this->menuLinks($item['children']));
            } else {
                $links[] = Arr::only($item, ['title', 'uri', 'icon']);
            }
        }

        return $links;
    }

    /**
     * Set admin title.
     *
     * @param string $title
     *
     * @return void
     */
    public static function setTitle($title)
    {
        self::$metaTitle = $title;
    }

    /**
     * Get admin title.
     *
     * @return string
     */
    public function title()
    {
        return self::$metaTitle ? self::$metaTitle : config('admin.title');
    }

    /**
     * @param null|string $favicon
     *
     * @return string|void
     */
    public static function favicon($favicon = null)
    {
        if (is_null($favicon)) {
            return static::$favicon;
        }

        static::$favicon = $favicon;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return $this->guard()->user();
    }

    /**
     * Attempt to get the guard from the local cache.
     *
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function guard()
    {
        $guard = config('admin.auth.guard') ?: 'admin';

        return Auth::guard($guard);
    }

    /**
     * Set navbar.
     *
     * @param Closure|null $builder
     *
     * @return Navbar
     */
    public function navbar(Closure $builder = null)
    {
        if (is_null($builder)) {
            return $this->getNavbar();
        }

        call_user_func($builder, $this->getNavbar());
    }

    /**
     * Get navbar object.
     *
     * @return \Encore\Admin\Widgets\Navbar
     */
    public function getNavbar()
    {
        if (is_null($this->navbar)) {
            $this->navbar = new Navbar();
        }

        return $this->navbar;
    }

    /**
     * Register the laravel-admin builtin routes.
     *
     * @return void
     *
     * @deprecated Use Admin::routes() instead();
     */
    public function registerAuthRoutes()
    {
        $this->routes();
    }

    /**
     * Register the laravel-admin builtin routes.
     *
     * @return void
     */
    public function routes()
    {
        $attributes = [
            'prefix'        => config('admin.route.prefix'),
            'domain'        => config('system.subdomain.admin') . config('system.domain'),
            'middleware'    => config('admin.route.middleware'),
            'as'            => 'admin.',
        ];

        app('router')->group($attributes, function ($router) {

            /* @var \Illuminate\Support\Facades\Route $router */
            $router->namespace('\Encore\Admin\Controllers')->group(function ($router) {

                /* @var \Illuminate\Routing\Router $router */
                $router->resource('system/administrators', 'UserController')->names('system.administrators');
                $router->resource('system/roles', 'RoleController')->names('system.roles');
                $router->resource('system/permissions', 'PermissionController')->names('system.permissions');
                $router->resource('system/menu', 'MenuController')->names('system.menu');

                $router->get('system/system-logs', 'LogViewerController@index')->name('system.system-log.index');
                $router->get('system/system-logs/{file}', 'LogViewerController@index')->name('system.system-log.file');
                $router->get('system/system-logs/{file}/tail', 'LogViewerController@tail')->name('system.system-log.tail');
                
                $router->get('system/operation-logs', 'OperationLogController@index')->name('system.operation-log.index');

                $router->get('system/artisan', 'ArtisanController@artisan')->name('system.artisan.index');
                $router->post('system/artisan', 'ArtisanController@runArtisan')->name('system.artisan.run');

                $router->post('_handle_form_', 'HandleController@handleForm')->name('handle-form');
                $router->post('_handle_action_', 'HandleController@handleAction')->name('handle-action');
                $router->get('_handle_selectable_', 'HandleController@handleSelectable')->name('handle-selectable');
                $router->get('_handle_renderable_', 'HandleController@handleRenderable')->name('handle-renderable');

                $router->post('_grid-sortable_', 'GridSortableController@sort')->name('laravel-admin-grid-sortable'); 

                $router->get('/ajax/tagging/{model}', 'AdminController@getTaggingItems')->name('getTaggingItems');
                $router->get('/ajax/relation-select-items/{model}/{primaryKey}/{column}', 'AdminController@getRelationSelectItems')->name('getRelationSelectItems');
                $router->get('/ajax/relation-select-item/{model}/{primaryKey}/{column}', 'AdminController@getRelationSelectItem')->name('getRelationSelectItem');

  
            });

            $router->resource('images', config('admin.contents.image.controller'));
            $router->resource('texts', config('admin.contents.text.controller'));
            $router->resource('html-texts', config('admin.contents.html_text.controller'));

            $router->get('/images/browse/modal', [config('admin.contents.image.controller'), 'browser'])->name('images.browse.modal');
            $router->get('/images/create/modal', [config('admin.contents.image.controller'), 'formModal'])->name('images.create.modal');
            $router->post('/images/create/modal', [config('admin.contents.image.controller'), 'storeModal'])->name('images.store.modal');
            $router->get('/images/{id}/edit/modal', [config('admin.contents.image.controller'), 'formModal'])->name('images.edit.modal');
            $router->put('/images/{id}/edit/modal', [config('admin.contents.image.controller'), 'storeModal'])->name('images.update.modal');

            $authController = config('admin.auth.controller', AuthController::class);

            /* @var \Illuminate\Routing\Router $router */
            $router->get('auth/login', $authController.'@getLogin')->name('login');
            $router->post('auth/login', $authController.'@postLogin')->name('login.post');
            $router->get('auth/logout', $authController.'@getLogout')->name('logout');
            $router->get('auth/setting', $authController.'@getSetting')->name('setting');
            $router->put('auth/setting', $authController.'@putSetting')->name('setting.put');
        });
    }

    /**
     * Extend a extension.
     *
     * @param string $name
     * @param string $class
     *
     * @return void
     */
    public static function extend($name, $class)
    {
        static::$extensions[$name] = $class;
    }

    /**
     * @param callable $callback
     */
    public static function booting(callable $callback)
    {
        static::$bootingCallbacks[] = $callback;
    }

    /**
     * @param callable $callback
     */
    public static function booted(callable $callback)
    {
        static::$bootedCallbacks[] = $callback;
    }

    /**
     * Bootstrap the admin application.
     */
    public function bootstrap()
    {
        $this->fireBootingCallbacks();

        require config('admin.bootstrap', admin_path('bootstrap.php'));

        $this->addAdminAssets();

        $this->fireBootedCallbacks();
    }

    /**
     * Add JS & CSS assets to pages.
     */
    protected function addAdminAssets()
    {
        $assets = Form::collectFieldAssets();

        self::css($assets['css']);
        self::js($assets['js']);
    }

    /**
     * Call the booting callbacks for the admin application.
     */
    protected function fireBootingCallbacks()
    {
        foreach (static::$bootingCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    /**
     * Call the booted callbacks for the admin application.
     */
    protected function fireBootedCallbacks()
    {
        foreach (static::$bootedCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    /*
     * Disable Pjax for current Request
     *
     * @return void
     */
    public function disablePjax()
    {
        if (request()->pjax()) {
            request()->headers->set('X-PJAX', false);
        }
    }

    public function permission(){
        return \Encore\Admin\Auth\Permission::class;
    }
}
