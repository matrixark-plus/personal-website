<?php

declare(strict_types=1);

return [
    // 默认速率限制配置
    'default' => [
        'handler' => \Hyperf\RateLimit\Handler\TokenBucketHandler::class,
        'cache' => \Hyperf\RateLimit\Handler\CacheCounterHandler::class,
        'options' => [
            'capacity' => 20,
            'rate' => 10,
            'key' => 'global',
        ],
    ],
    // 评论相关速率限制配置
    'comment' => [
        'handler' => \Hyperf\RateLimit\Handler\TokenBucketHandler::class,
        'cache' => \Hyperf\RateLimit\Handler\CacheCounterHandler::class,
        'options' => [
            'capacity' => 10,
            'rate' => 5,
            'key' => 'comment',
        ],
    ],
];