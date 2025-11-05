<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'http' => [
        // 全局中间件
        Hyperf\HttpServer\Middleware\BodyParserMiddleware::class,
        Hyperf\Session\Middleware\SessionMiddleware::class,
        
        // 错误处理中间件
        App\Middleware\ErrorHandleMiddleware::class,
        
        // 跨域中间件
        App\Middleware\CorsMiddleware::class,
    ],
    'group' => [
        // JWT认证中间件别名
        'jwt' => [
            App\Middleware\JwtAuthMiddleware::class,
        ],
        

    ],
    'alias' => [
        // 中间件别名映射
        'jwt' => App\Middleware\JwtAuthMiddleware::class,
        'cors' => App\Middleware\CorsMiddleware::class,
    ],
];