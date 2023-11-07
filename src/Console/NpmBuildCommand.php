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
        '~/bin/npm',
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
        $command = null;

        foreach ($this->commands as $command) {
            $process = Process::run("{$command} -v");
            if ($process->successful()) break;
        }

        if (!$command) {
            $this->error("Npm command not found");
            return false;
        }

        $this->info("Running '{$command} run build'");

        $process = Process::run("{$command} run build", function (string $type, string $output) use($that) {
            $that->line($output);
        });

        return $process->successful();
    }
}