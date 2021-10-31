<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\Output;

class ArtisanController extends Controller
{
    public function artisan()
    {
        return Admin::content(function (Content $content) {
            $content->header('Artisan terminal');

            $content->row(view('admin::artisan', ['commands' => $this->organizedCommands()]));
        });
    }

    public function runArtisan()
    {
        $command = Request::get('c', 'list');

        // If Exception raised.
        if (1 === Artisan::handle(
            new ArgvInput(explode(' ', 'artisan '.trim($command))),
            $output = new StringOutput()
        )) {
            return $this->renderException(new Exception($output->getContent()));
        }

        return sprintf('<pre>%s</pre>', $output->getContent());
    }

    protected function organizedCommands()
    {
        $commands = array_keys(Artisan::all());

        $groups = $others = [];

        foreach ($commands as $command) {
            $parts = explode(':', $command);

            if (isset($parts[1])) {
                $groups[$parts[0]][] = $command;
            } else {
                $others[] = $command;
            }
        }

        foreach ($groups as $key => $group) {
            if (count($group) === 1) {
                $others[] = $group[0];

                unset($groups[$key]);
            }
        }

        ksort($groups);
        sort($others);

        return compact('groups', 'others');
    }

    protected function renderException(Exception $exception)
    {
        return sprintf(
            "<div class='callout callout-warning'><i class='icon fa fa-warning'></i>&nbsp;&nbsp;&nbsp;%s</div>",
            str_replace("\n", '<br />', $exception->getMessage())
        );
    }
}

class StringOutput extends Output
{
    public $output = '';

    public function clear()
    {
        $this->output = '';
    }

    protected function doWrite($message, $newline)
    {
        $this->output .= $message.($newline ? "\n" : '');
    }

    public function getContent()
    {
        return trim($this->output);
    }
}