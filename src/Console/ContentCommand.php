<?php

namespace Encore\Admin\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Schema;

class ContentCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'admin:content {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make admin content';

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var string
     */
    protected $modelNamePlural;

    /**
     * @var string
     */
    protected $slug;

    protected $excludeColumnTranslations = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->modelName = $this->getModelName();
        $this->modelNamePlural = Str::pluralStudly($this->modelName);
        $this->controllerName = $this->getControllerName();
        $this->slug = Str::kebab(Str::pluralStudly($this->modelName));

        exec("php artisan admin:model {$this->modelName}");

        $this->model = "App\\Models\\{$this->modelName}";

        $this->generator = new ResourceGenerator("App\\Models\\{$this->modelName}");

        $table = Str::snake(Str::pluralStudly(class_basename($this->modelName)));

        $date = date('Y_m_d_his');

        $this->call('admin:migration', [
            'name' => $this->modelName,
            '--create' => $table,
            '--date' => $date,
        ]);

        $message = 'Did you finish entering the table fields?';

        do {

            if ($this->confirm($message)) {
                try {
                    $this->migrate('database/migrations/' . $date . '_' . "create_{$table}_table");
                    if (parent::handle() !== false) {

                        $title = $this->model::getContentTitle();
                        $titlePlural = $this->model::getContentTitlePlural();

                        $this->line('');
                        $this->comment('Add the following route to app/Admin/routes.php:');
                        $this->line('');
                        $this->info("    \$router->resource('{$this->slug}', {$this->controllerName}::class);");
                        $this->info("    \$router->get('/{$this->slug}/create/modal', '{$this->controllerName}@formModal')->name('{$this->slug}.create.modal')");
                        $this->info("    \$router->post('/{$this->slug}/create/modal', '{$this->controllerName}@storeModal')->name('{$this->slug}.store.modal')");
                        $this->info("    \$router->get('/{$this->slug}/{id}/edit/modal', '{$this->controllerName}@formModal')->name('{$this->slug}.edit.modal')");
                        $this->info("    \$router->put('/{$this->slug}/{id}/edit/modal', '{$this->controllerName}@storeModal')->name('{$this->slug}.update.modal')");
                        $this->line('');
                        $this->comment('Add the following lines to default language files (json)');
                        $this->line('');
                        $this->info("    \"{$title}\" : \"{$title}\",");
                        $this->info("    \"{$titlePlural}\" : \"{$titlePlural}\",");
                        foreach (Schema::getColumnListing($table) as $column){
                            if (in_array($column, $this->excludeColumnTranslations)) continue;
                            $column = \Str::of($column)->replaceLast('_id', '');
                            $label = $this->formatLabel($column);
                            $label = ucfirst(strtolower(\Str::headline($label)));
                            $this->info("    \"{$this->modelName}.{$column}\" : \"{$label}\",");
                        }
                    }
                    break;
                } catch (\Exception $e) {
                    $this->info($e->getMessage());
                    $message = 'Did you corrected it?';
                    sleep(1);
                    continue;
                }
            }
            break;
        } while (true);
    }

    protected function formatLabel($value)
    {
        return ucfirst(str_replace(['-', '_'], ' ', $value));
    }

    protected function migrate($file)
    {
        return $this->call('migrate', ['--path' => "{$file}.php"]);
    }

    public function getTitle()
    {
        return $this->model::getContentTitle();
    }

    /**
     * @return array|string|null
     */
    protected function getControllerName()
    {
        return "{$this->argument('name')}Controller";
    }

    /**
     * @return array|string|null
     */
    protected function getModelName()
    {
        return $this->argument('name');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        return str_replace(
            [
                'DummyModelNamespace',
                'DummyTitle',
                'DummyModel',
                'DummyGrid',
                'DummyShow',
                'DummyForm',
            ],
            [
                "App\\Models\\{$this->modelName}",
                $this->getTitle(),
                class_basename($this->modelName),
                $this->indentCodes($this->generator->generateGrid()),
                $this->indentCodes($this->generator->generateShow()),
                $this->indentCodes($this->generator->generateForm()),
            ],
            $stub
        );
    }

    /**
     * @param string $code
     *
     * @return string
     */
    protected function indentCodes($code)
    {
        $indent = str_repeat(' ', 8);

        return rtrim($indent . preg_replace("/\r\n/", "\r\n{$indent}", $code));
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/contentController.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return config('admin.route.namespace');
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return $this->controllerName;
    }
}
