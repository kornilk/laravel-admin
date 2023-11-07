<?php

namespace Encore\Admin\Console;

use Illuminate\Console\Command;

class RefreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the app';

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

        if ($this->call('git:pull') !== 0) {
            return 1;
        }

        if (!$this->call('npm:build') !== 0) {
            return 1;
        }

        $this->info("Succesfully refreshed the application.");
        return 0;
    }
}