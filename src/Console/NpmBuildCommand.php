<?php

namespace Encore\Admin\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

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
     * Log from process
     * 
     * @var array
     */
    private $log = [];

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

            $this->error("An error occurred while executing 'npm run build'. \nLogs:");

            foreach($this->log as $logLine) {
                $this->info($logLine);
            }

            return;
        }

        $this->info("Succesfully builded the application.");
    }

    /**
     * Run git pull process
     * 
     * @return boolean
     */

    private function runPull()
    {
        $process = new Process(['npm', 'run build']);
        $this->info("Running 'npm run build'");

        $process->run(function($type, $buffer) {
            $this->log[] = $buffer;
            
        });

        return $process->isSuccessful();
    }
}