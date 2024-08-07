<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{

    protected $disablePermissionCheck = true;

    public function __construct()
    {
        $this->model = config('admin.database.users_model');
        parent::__construct();

    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new $this->model());

        if (!\Auth::guard('admin')->user()->isAdministrator()) {
            $grid->model()->whereDoesntHave('roles', function ($query) {
                $query->where('slug', 'administrator');
            })->whereDoesntHave('permissions', function ($query) {
                $query->where('id', '*');
            });
        }

        $grid->column('name', $this->model::label('name'))->sortable()->edit();
        $grid->column('email', $this->model::label('email'))->sortable();
        $grid->column('created_at', trans('admin.created_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
        });

        $grid->disableExport();

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        $grid->filter(function ($filter) {

            $filter->where(function ($query) {

                $query->where('name', 'like', "%{$this->input}%")
                    ->orWhere('email', 'like', "%{$this->input}%");
            }, __('admin.Search'));
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show($this->model::findOrFail($id));

        if ($this->model::where('id', $id)->first()->isAdministrator() && !\Auth::guard('admin')->user()->isAdministrator()) {
            \Encore\Admin\Auth\Permission::error();
        }

        $show->field('name', $this->model::label('name'));
        $show->field('email', $this->model::label('email'));

        $show->field('roles', trans('admin.roles'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();

        $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();

        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $this->model());

        $form->editing(function (Form $form) {
            if ($form->model()->isAdministrator() && !\Auth::guard('admin')->user()->isAdministrator()) {
                \Encore\Admin\Auth\Permission::error();
            }
        });

        $roles = (new $roleModel)->newQuery();
        $permissions = (new $permissionModel)->newQuery();

        if (!\Auth::guard('admin')->user()->isAdministrator()) {

            foreach (config('admin.auth.admin_permissions') as $permission_slug) {
                $permissions->where('slug', '!=', $permission_slug);
            }
         
            foreach (config('admin.auth.hidden_permissions') as $permission_slug) {
                $permissions->where('slug', '!=', $permission_slug);
            }

            foreach (config('admin.auth.admin_roles') as $role_slug) {
                $roles->where('slug', '!=', $role_slug);
            }

            foreach (config('admin.auth.hidden_roles') as $role_slug) {
                $roles->where('slug', '!=', $role_slug);
            }

        }

        foreach (config('admin.auth.default_permissions') as $permission_slug) {
            $permissions->where('slug', '!=', $permission_slug);
        }

        /*===============================================================*/

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->text('name', trans('admin.name'))->rules('required');

        $form->text('email', $this->model::label('email'))
            ->creationRules(['required', 'email', 'max:190', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', 'email', 'max:190', "unique:{$connection}.{$userTable},email,{{id}}"]);

        $form->imageSimple('avatar', trans('admin.avatar'));

        $form->password('password', $this->model::label('password'))
            ->creationRules('required|confirmed|max:100')
            ->updateRules('sometimes|confirmed|max:100');
        $form->password('password_confirmation', $this->model::label('password_confirmation'))
            ->creationRules('required');

        $form->ignore(['password_confirmation']);

        $form->listbox('roles', trans('admin.roles'))->options($roles->get()->pluck('name', 'id'))->addVariables(['optionStyles' => [
            'color-red' => (new $roleModel)->whereIn('slug', config('admin.auth.hidden_roles'))->get()->pluck('id')->toArray(),
        ]]);

        $form->listbox('permissions', trans('admin.permissions'))->options($permissions->get()->pluck('name', 'id'))->addVariables(['optionStyles' => [
            'color-red' => (new $permissionModel)->whereIn('slug', config('admin.auth.hidden_permissions'))->get()->pluck('id')->toArray(),
        ]]);

        $form->saving(function (Form $form) use ($permissionModel, $roleModel) {

            $permissions = $form->permissions;
            $roles = $form->roles;

            foreach ($permissions as $index => $value) {
                if (is_null($permissions[$index])) unset($permissions[$index]);
            }

            foreach ($roles as $index => $value) {
                if (is_null($roles[$index])) unset($roles[$index]);
            }

            foreach (config('admin.auth.default_permissions') as $permission_slug) {

                $permission = $permissionModel::where('slug', $permission_slug)->first();
                if ($permission) {
                    if (in_array($permission->id, $permissions)) continue;
                    $permissions[] = $permission->id;
                }
            }

            if (!\Auth::guard('admin')->user()->isAdministrator()) {

                foreach (config('admin.auth.admin_permissions') as $permission_slug) {

                    $permission = $permissionModel::where('slug', $permission_slug)->first();

                    if ($permission && ($key = array_search($permission->id, $permissions)) !== false) {
                        unset($permissions[$key]);
                    }
                }

                foreach (config('admin.auth.hidden_permissions') as $permission_slug) {

                    $permission = $permissionModel::where('slug', $permission_slug)->first();

                    if ($permission && ($key = array_search($permission->id, $permissions)) !== false) {
                        unset($permissions[$key]);
                    }
                }
    
                foreach (config('admin.auth.admin_roles') as $role_slug) {

                    $role = $roleModel::where('slug', $role_slug)->first();

                    if ($role && ($key = array_search($role->id, $roles)) !== false) {
                        unset($roles[$key]);
                    }
                }

                foreach (config('admin.auth.hidden_roles') as $role_slug) {

                    $role = $roleModel::where('slug', $role_slug)->first();

                    if ($role && ($key = array_search($role->id, $roles)) !== false) {
                        unset($roles[$key]);
                    }
                }

            }

            $form->permissions = $permissions;
            $form->roles = $roles;

            if (!empty($form->password) && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            } else if ($form->model()->id) {
                $form->password = $form->model()->password;
            }

        });

        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        function script() 
        {
            return <<<SCRIPT
        
                $(function() {
                    setTimeout(function(){
                        $('input[name="password"]').val('');
                        $('input[name="password_confirmation"]').val('');
                    }, 1000)
                });

                SCRIPT;
        }

        \Admin::script(script());

        return $form;
    }
}
