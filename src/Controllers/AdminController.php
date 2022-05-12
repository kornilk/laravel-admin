<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Extensions\ModalForm\Form\ModalForm;
use Encore\Admin\Form;
use Encore\Admin\Grid\Actions\BatchForceDelete;
use Encore\Admin\Grid\Actions\BatchRestore;
use Encore\Admin\Grid\Actions\ForceDelete;
use Encore\Admin\Grid\Actions\Restore;
use Encore\Admin\Layout\Content;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminController extends Controller
{
    use HasResourceActions;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Title';

    /**
     * Set description for following 4 action pages.
     *
     * @var array
     */
    protected $description = [
        //        'index'  => 'Index',
        //        'show'   => 'Show',
        //        'edit'   => 'Edit',
        //        'create' => 'Create',
    ];

    protected $titlePlural;
    protected $slug;
    protected $permissionName;
    protected $id;
    protected $form;
    protected $disablePermissionCheck = false;

    public function __construct()
    {
        if (property_exists($this, 'model') && method_exists($this->model, 'getContentTitle')) $this->title = __($this->model::getContentTitle());
        if (property_exists($this, 'model') && method_exists($this->model, 'getContentTitlePlural')) $this->titlePlural = __($this->model::getContentTitlePlural());
        if (property_exists($this, 'model') && method_exists($this->model, 'getContentSlug')) $this->slug = $this->model::getContentSlug();
        if (property_exists($this, 'model') && method_exists($this->model, 'getContentPermissionName')) $this->permissionName = $this->model::getContentPermissionName();

        $permissionName = $this->permissionName;

        $this->middleware(function ($request, $next) use ($permissionName) {

            if (empty($permissionName)) return $next($request);

            if (!\Admin::permission()::hasAccessBySlug($permissionName)) {
                return \Admin::permission()::error();
            }

            return $next($request);
        });
    }

    /**
     * Get content title.
     *
     * @return string
     */
    protected function title()
    {
        return $this->title;
    }

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        $body = $this->grid();
        $body->disableExport();
        $content->breadcrumb(...$this->getBreadcrumb());

        if (!empty($this->description['index'])) $content->description($this->description['index']);

        $body->model()->orderBy('created_at', 'desc');

        $body->filter(function ($filter) {

            if (property_exists($this, 'model') && in_array(SoftDeletes::class, class_uses_deep($this->model)))
                $filter->scope('trashed', __('admin.Recycle Bin'))->onlyTrashed();
        });

        $body->actions(function ($actions) {

            if (\request('_scope_') == 'trashed') {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();
                $actions->add(new Restore($actions->getKey()));
                $actions->add(new ForceDelete($actions->getKey()));
            }
        });

        $body->batchActions(function ($batch) {

            if (\request('_scope_') == 'trashed') {
                $batch->disableDelete();
                $batch->add(new BatchRestore());
                $batch->add(new BatchForceDelete());
            }
        });

        if (!$this->disablePermissionCheck)
            manageActionsByPermissions($body, $this->permissionName);

        return $content
            ->title($this->title())
            ->body($body);
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        $this->id = $id;
        $body = $this->detail($id);
        $content->breadcrumb(...$this->getBreadcrumb($id, $body));

        if (!empty($this->description['show'])) $content->description($this->description['show']);

        if (!$this->disablePermissionCheck)
            manageActionsByPermissions($body, $this->permissionName);

        return $content
            ->title($this->title())
            ->body($body);
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $this->id = $id;
        $body = $this->detail($id);
        $content->breadcrumb(...$this->getBreadcrumb($id, $body, __('admin.edit')));

        if (!empty($this->description['edit'])) $content->description($this->description['edit']);

        $form = $this->form()->edit($id);

        $this->setFormDefaultSettings($form);

        return $content
            ->title($this->title())
            ->body($form);
    }

    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        $content->breadcrumb(...$this->getBreadcrumb(null, null, __('admin.create')));
        $form = $this->form();

        if (!empty($this->description['create'])) $content->description($this->description['create']);

        $this->setFormDefaultSettings($form);

        return $content
            ->title($this->title())
            ->body($form);
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new $this->model());

        $this->setForm($form);

        return $form;
    }

    protected function setForm($form)
    {

        return $form;
    }

    public function formModal($id = null)
    {

        $this->id = $id;
        return new ModalForm(new $this->model(), function (ModalForm $form) use ($id) {

            $this->setForm($form);

            $routeType = $id ? 'update' : 'store';
            $parameters = [];

            if ($id) $parameters['id'] = $id;

            $form->setAction(route("admin.{$form->model()::getContentSlug()}.{$routeType}.modal", $parameters));

            if ($id) $form->edit($id);

            $form->large();

            $this->setFormDefaultSettings($form);

            $form->saved(function ($form) {
                return $this->modalSaveRespose($form);
            });
        });
    }

    public function storeModal($id = null)
    {

        $this->id = $id;
        return $this->formModal($id)->store();
    }

    protected function setFormDefaultSettings($form)
    {
        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        if (!$this->disablePermissionCheck)
            manageActionsByPermissions($form, $this->permissionName);

        $form->copyFieldAttributesToTranslatedFields();

        return $form;
    }

    protected function getBreadcrumb($id = null, $data = null, $current = FALSE)
    {
        $breadcrumb = [
            ['text' => $this->title]
        ];

        if (!empty($data)) {

            $item = false;

            if (!empty($data->getModel()->title)) {
                $item = ['text' => \Str::limit($data->getModel()->title, 30, '...')];
            } else if (!empty($data->getModel()->name)) {
                $item = ['text' => \Str::limit($data->getModel()->name, 30, '...')];
            } else if (!empty($data->getModel()->id)) {
                $item = ['text' => $data->getModel()->id];
            } else if (!empty($id)) {
                $item = ['text' => $id];
            }

            if ($item) {
                $breadcrumb[] = $item;
            }
        }

        if ($current) $breadcrumb[] = ['text' => $current];

        return $breadcrumb;
    }

    protected function getYesNoSwitch()
    {
        return [
            'on'  => ['value' => 1, 'text' => __('yes'), 'color' => 'success'],
            'off' => ['value' => 0, 'text' => __('no'), 'color' => 'danger'],
        ];
    }

    protected function modalSaveRespose($form, $message = null)
    {

        if (empty($message)) $message = __('admin.save_succeeded');

        $res = new Response(json_encode([
            'status' => true,
            'message' => $message,
            'modelId' => $form->model()->id,
            'data' => $form->model(),
        ]));
        $res->header('Content-Type', 'application/json');
        return $res;
    }
}
