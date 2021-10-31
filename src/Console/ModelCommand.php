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

        $this->modelModelName = $this->argument('name');
        $this->title = ucfirst(strtolower(\Str::headline($this->modelModelName)));
        $this->titlePlural = ucfirst(strtolower(\Str::headline(Str::pluralStudly($this->modelModelName))));
        $this->slug = Str::slug($this->titlePlural);

        return str_replace(
            [
                'DummyTitlePlural',
                'DummyTitle',
                'DummyModelName',
                'DummySlug',
            ],
            [
                $this->titlePlural,
                $this->title,
                $this->modelModelName,
                $this->slug,
            ],
            $stub
        );
    }

    protected function createMigration()
    {
        
    }
}
