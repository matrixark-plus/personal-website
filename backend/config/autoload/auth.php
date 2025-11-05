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
    // 默认使用的认证守卫
    'default' => 'jwt',
    // 认证守卫配置
    'guards' => [
        'jwt' => [
            'driver' => 'jwt',
            'provider' => 'users',
            'secret' => env('JWT_SECRET', 'your-secret-key-here'),
            'ttl' => 7200, // token有效期，单位秒
            'refresh_ttl' => 2592000, // refresh token有效期，单位秒
            'alg' => 'HS256', // 加密算法
            'header_algorithm' => 'Bearer', // 请求头算法标识
            'blacklist_enabled' => true, // 是否启用token黑名单
            'blacklist_grace_period' => 10, // 黑名单宽限期，单位秒
            'blacklist_prefix' => 'jwt_blacklist:', // 黑名单缓存前缀
        ],
    ],
    // 认证提供者配置
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Model\User::class, // 用户模型
        ],
    ],
];