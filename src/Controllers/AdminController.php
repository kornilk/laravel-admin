<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Grid\Actions\BatchForceDelete;
use Encore\Admin\Grid\Actions\BatchRestore;
use Encore\Admin\Grid\Actions\ForceDelete;
use Encore\Admin\Grid\Actions\Restore;
use Encore\Admin\Layout\Content;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;

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

    public function __construct()
    {
        if (property_exists($this, 'model') && method_exists($this->model, 'getContentTitle')) $this->title = $this->model::getContentTitle();
        if (property_exists($this, 'model') && method_exists($this->model, 'getContentTitlePlural')) $this->titlePlural = $this->model::getContentTitlePlural();
        if (property_exists($this, 'model') && method_exists($this->model, 'getContentSlug')) $this->slug = $this->model::getContentSlug();
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

        manageActionsByPermissions($body, $this->slug);

        $body->filter(function($filter) {

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

        $body->batchActions (function($batch) {

            if (\request('_scope_') == 'trashed') {
                $batch->disableDelete();
                $batch->add(new BatchRestore());
                $batch->add(new BatchForceDelete());
            }
            
        });

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
        $body = $this->detail($id);
        $content->breadcrumb(...$this->getBreadcrumb($id, $body));

        if (!empty($this->description['show'])) $content->description($this->description['show']);

        manageActionsByPermissions($body, $this->slug);

        function inlineScript() 
        {

            return <<<SCRIPT
                var item = $('.detail-container div.article-element.article-object div.article-element').each(function( index ) {
                    var h = '';

                    try {
                        h = JSON.parse(item.data('json'));
                      }
                      catch(err) {
                      }

                    $(this).html(h);
                });

                
                SCRIPT;
        }

        \Admin::script(inlineScript());

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
        $body = $this->detail($id);
        $content->breadcrumb(...$this->getBreadcrumb($id, $body, __('admin.edit')));

        if (!empty($this->description['edit'])) $content->description($this->description['edit']);

        $form = $this->form()->edit($id);

        manageActionsByPermissions($form, $this->slug);

        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        manageActionsByPermissions($form, $this->slug);

        $form->copyFieldAttributesToTranslatedFields();
           
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

        manageActionsByPermissions($form, $this->slug);

        if (!empty($this->description['create'])) $content->description($this->description['create']);

        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $form->copyFieldAttributesToTranslatedFields();

        return $content
            ->title($this->title())
            ->body($form);
    }

    public function getBreadcrumb($id = null, $data = null, $current = FALSE)
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
            'on'  => ['value' => 1, 'text' => __('content.Yes'), 'color' => 'success'],
            'off' => ['value' => 0, 'text' => __('content.No'), 'color' => 'danger'],
        ];
    }

    public function modalSaveRespose($form, $message = null){

        if (empty($message)) $message = __('admin.save_succeeded');

        $res = new Response(json_encode([
            'status' => true,
            'message' => $message,
            'modelId' => $form->model()->id,
            'data' => $form->model()
        ]));
        $res->header('Content-Type', 'application/json');
        return $res;
    }
}
