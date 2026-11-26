<?php

return [
    'enabled' => env('PROFAC_POS_ENABLED', false),
    'secret' => env('PROFAC_POS_SECRET'),
    'peer_url' => env('PROFAC_POS_PEER_URL'),
    'store_id' => 1,
    'cli_php' => env('PROFAC_POS_PHP_CLI', '/usr/bin/php'),
];
