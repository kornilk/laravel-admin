<?php

namespace Encore\Admin\Console;

use Illuminate\Console\GeneratorCommand;
use Str;

class MigrationCommand extends GeneratorCommand
{

    protected $rootNamespace = 'database\\migrations';
   /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'admin:migration {name : The name of the migration}
    {--create= : The table to be created} {--date}';

    public function handle()
    {
        if ($this->isReservedName($this->getNameInput())) {
            $this->error('The name "'.$this->getNameInput().'" is reserved by PHP.');

            return false;
        }

        $this->migrationClass = 'Create' . Str::pluralStudly($this->getNameInput()) . 'Table';

        $date = $this->input->getOption('date');
        if (empty($date)) $date = date('Y_m_d_his');

        $name = $date . '_create_' . $this->input->getOption('create') . '_table';
        $name = $this->qualifyClass($name);

        $path = $this->getPath($name);

        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') ||
             ! $this->option('force')) &&
             $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type.' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        $this->info($this->type.' created successfully.');

        if (in_array(CreatesMatchingTest::class, class_uses_recursive($this))) {
            $this->handleTestCreation($path);
        }
        
    }

       /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        return base_path() .'/'.str_replace('\\', '/', $name).'.php';
    }

    protected function rootNamespace()
    {
        return 'database\\migrations\\';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/contentMigration.stub';
    }

    protected function replaceClass($stub, $name)
    {
        return str_replace(
            [
                'DummyMigrationName',
                'DummyModelName',
                'DummyTableName',
            ],
            [
                $this->migrationClass,
                $this->getNameInput(),
                $this->input->getOption('create'),
            ],
            $stub
        );
    }
}
