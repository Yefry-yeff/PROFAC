<?php

return [
    'enabled' => env('PROFAC_POS_ENABLED', false),
    'secret' => env('PROFAC_POS_SECRET'),
    'peer_url' => env('PROFAC_POS_PEER_URL'),
    'store_id' => 1,
    'cli_php' => env('PROFAC_POS_PHP_CLI', PHP_OS_FAMILY === 'Windows' ? dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'php.exe' : '/usr/bin/php'),
];
