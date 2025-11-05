<?php

declare(strict_types=1);

return [
    // 默认的速率限制配置
    'default' => [
        // 限制数量
        'limit' => 100,
        // 时间窗口长度（秒）
        'time' => 1,
        // 存储方式：redis 或 cache
        'store' => 'redis',
    ],
    
    // 登录接口的限流配置
    'login' => [
        'limit' => 5,
        'time' => 60,
    ],
    
    // 注册接口的限流配置
    'register' => [
        'limit' => 3,
        'time' => 60,
    ],
    
    // OAuth相关接口的限流配置
    'oauth' => [
        'limit' => 10,
        'time' => 60,
    ],
    
    // 评论相关接口的限流配置
    'comment' => [
        'limit' => 10,
        'time' => 60,
    ],
    
    // 邮箱验证码接口的限流配置
    'email_verify' => [
        'limit' => 2,
        'time' => 60,
    ],
    
    // 管理员接口的限流配置
    'admin' => [
        'limit' => 30,
        'time' => 60,
    ],
];