<?php

namespace Encore\Admin\Console;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Str;

class ModelCommand extends ModelMakeCommand
{

    protected $name = 'admin:model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/contentModel.stub';
    }

    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        $this->modelName = $this->argument('name');
        $this->modelNamePlural = Str::pluralStudly($this->modelName);
        $this->slug = Str::kebab($this->modelNamePlural);

        return str_replace(
            [
                'DummyModelNamePlural',
                'DummyModelName',
                'DummySlug',
            ],
            [
                $this->modelNamePlural,
                $this->modelName,
                $this->slug,
            ],
            $stub
        );
    }

    protected function createMigration()
    {
        
    }
}
