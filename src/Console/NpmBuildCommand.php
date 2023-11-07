<?php

namespace Encore\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class NpmBuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'npm:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the application with NPM';

    /**
     * Npm commands
     *
     * @var array
     */
    protected $commands = [
        'npm',
        'sh vendor/kornilk/laravel-admin/scripts/npm.sh',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(!$this->runBuild()) {

            $this->error("An error occurred while executing 'npm run build'.");

            return;
        }

        $this->info("Succesfully builded the application.");
        return 1;
    }

    /**
     * Run git pull process
     * 
     * @return boolean
     */

    private function runBuild()
    {
        $that = $this;
        $NpmCommand = null;

        foreach ($this->commands as $command) {

            $process = Process::run("{$command} -v");

            $this->info("Try command '{$command} -v'");

            if ($process->successful()) {
                $NpmCommand = $command;
                break;
            } else {
                $this->error($process->errorOutput());
            }
        }

        if (!$NpmCommand) {
            $this->error("Valid npm command not found");
            return false;
        }

        $this->info("Running '{$NpmCommand} run build'");

        $process = Process::run("{$NpmCommand} run build", function (string $type, string $output) use($that) {
            $that->line($output);
        });

        return $process->successful();
    }
}