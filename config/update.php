<?php

return [
    'git' => [
        'command' => env('UPDATE_GIT_COMMAND', "git pull origin $(git rev-parse --abbrev-ref HEAD) 2>&1"),
    ],
    'npm' => [
        'command' => env('UPDATE_NPM_BUILD_COMMAND', "{COMMAND} -v 2>&1"),
        'test' => env('UPDATE_NPM_TEST_COMMAND', "{COMMAND} -v 2>&1"),
        'commands' => [
            env('UPDATE_NPM_COMMANDS_1', "npm"),
            env('UPDATE_NPM_COMMANDS_2', 'export PATH=$PATH:~/bin && npm'),
            env('UPDATE_NPM_COMMANDS_3', ""),
            env('UPDATE_NPM_COMMANDS_4', ""),
        ]
    ],
];