<?php

declare(strict_types=1);

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'port' => (int)env('REDIS_PORT', 6379),
        'auth' => env('REDIS_AUTH', null),
        'database' => (int)env('REDIS_DATABASE', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 20,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];
