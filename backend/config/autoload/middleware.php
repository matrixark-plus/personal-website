<?php

declare(strict_types=1);
/**
 * 中间件配置文件
 */
return [
    // 全局中间件，所有请求都会经过的中间件
    'http' => [
        // 错误处理中间件应放在最前面，确保所有异常都能被捕获
        App\Middleware\ErrorHandlerMiddleware::class,
        App\Middleware\RequestLogMiddleware::class,
        App\Middleware\CorsMiddleware::class,
        App\Middleware\BloomFilterMiddleware::class,
    ],
    
    // 路由中间件，可以针对特定路由使用
    'middleware' => [
        'jwt' => App\Middleware\JwtAuthMiddleware::class,
        'admin' => App\Middleware\JwtAuthMiddleware::class,
        'rate_limit' => \Hyperf\RateLimit\Middleware\RateLimitMiddleware::class,
        'error_handler' => App\Middleware\ErrorHandlerMiddleware::class,
    ],
];