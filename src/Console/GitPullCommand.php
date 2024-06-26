<?php

namespace Encore\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class GitPullCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull files from GIT';

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
        if (!$this->runPull()) {

            $this->error("An error occurred while executing 'git pull'");

            return 1;
        }

        $this->info("Succesfully updated the application.");
        return 0;
    }

    /**
     * Run git pull process
     * 
     * @return boolean
     */

    private function runPull()
    {
        $this->info("Running 'git pull'");
        $that = $this;

        $output=null;
        $retval=null;
        exec(config('update.git.command'), $output, $retval);
       
        foreach ($output as $line) {
            $that->info($line);
        }

        // $process = Process::run('git pull origin $(git rev-parse --abbrev-ref HEAD)', function (string $type, string $output) use($that) {
        //     $that->line($output);
        // });

        // return $process->successful();

        return $retval === 0;
    }
}
