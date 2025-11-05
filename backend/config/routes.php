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
use Hyperf\HttpServer\Router\Router;

// 健康检查路由 - 应用默认速率限制
Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index', ['middleware' => ['rate_limit:default']]);

Router::get('/favicon.ico', function () {
    return '';
}, ['middleware' => ['rate_limit:default']]);

// 认证相关路由
Router::addGroup('/api/auth', function () {
    // 登录接口 - 严格限流
    Router::post('/login', 'App\Controller\AuthController@login', ['middleware' => ['rate_limit:login']]);
    
    // 注册接口 - 严格限流
    Router::post('/register', 'App\Controller\AuthController@register', ['middleware' => ['rate_limit:register']]);
    
    // 登出接口
    Router::post('/logout', 'App\Controller\AuthController@logout', ['middleware' => ['jwt']]);
    
    // 刷新令牌
    Router::post('/refresh', 'App\Controller\AuthController@refreshToken', ['middleware' => ['jwt']]);
    
    // 获取当前用户信息
    Router::get('/me', 'App\Controller\AuthController@getProfile', ['middleware' => ['jwt']]);
});

// OAuth相关路由
Router::addGroup('/api/oauth', function () {
    // OAuth登录重定向
    Router::get('/{platform}/redirect', 'App\Controller\OAuthController@redirect', ['middleware' => ['rate_limit:oauth']]);
    
    // OAuth回调
    Router::get('/{platform}/callback', 'App\Controller\OAuthController@callback', ['middleware' => ['rate_limit:oauth']]);
    
    // 绑定OAuth账号
    Router::post('/{platform}/bind', 'App\Controller\OAuthController@bind', ['middleware' => ['jwt', 'rate_limit:oauth']]);
});

// 评论管理相关路由 - 应用评论特定的速率限制
Router::addGroup('/api/comments', function () {
    // 获取评论列表（公开访问）
    Router::get('', 'App\Controller\CommentController@index');
    
    // 创建评论（需要JWT认证）
    Router::post('', 'App\Controller\CommentController@store', ['middleware' => ['jwt', 'rate_limit:comment']]);
    
    // 获取评论详情
    Router::get('/{id}', 'App\Controller\CommentController@show');
    
    // 更新评论
    Router::put('/{id}', 'App\Controller\CommentController@update', ['middleware' => ['jwt', 'rate_limit:comment']]);
    
    // 删除评论
    Router::delete('/{id}', 'App\Controller\CommentController@destroy', ['middleware' => ['jwt', 'rate_limit:comment']]);
    
    // // 获取待审核评论列表（管理员权限）
    Router::get('/pending/list', 'App\Controller\CommentController@getPendingComments', ['middleware' => ['admin']]);
    
    // // 审核通过评论（管理员权限）
    Router::put('/{id}/approve', 'App\Controller\CommentController@approveComment', ['middleware' => ['admin']]);
    
    // // 拒绝评论（管理员权限）
    Router::put('/{id}/reject', 'App\Controller\CommentController@rejectComment', ['middleware' => ['admin']]);
    
    // // 批量审核评论（管理员权限）
    Router::post('/batch-review', 'App\Controller\CommentController@batchReviewComments', ['middleware' => ['admin']]);
    
    // // 获取评论的回复
    Router::get('/{id}/replies', 'App\Controller\CommentController@getReplies');
    
    // 回复评论（需要JWT认证）
    Router::post('/{id}/reply', 'App\Controller\CommentController@replyComment', ['middleware' => ['jwt', 'rate_limit:comment']]);
});

// API v1 路由组
Router::addGroup('/api/v1', function () {
    // 邮箱相关路由
    Router::post('/email/send-verify-code', 'App\Controller\Api\V1\EmailController@sendVerifyCode', ['middleware' => ['rate_limit:email_verify']]);
    Router::post('/email/send', 'App\Controller\Api\V1\EmailController@sendEmail', ['middleware' => ['admin']]);
    
    // 订阅相关路由
    Router::post('/subscribe/blog', 'App\Controller\Api\V1\SubscribeController@subscribeBlog', ['middleware' => ['rate_limit:email_verify']]);
    Router::get('/subscribe/confirm', 'App\Controller\Api\V1\SubscribeController@confirmSubscribe');
    
    // 联系表单相关路由
    Router::post('/contact/submit', 'App\Controller\Api\V1\ContactController@submitContact', ['middleware' => ['rate_limit:default']]);
    
    // 社交媒体分享路由
    Router::get('/social/share/config', 'App\Controller\Api\V1\SocialShareController@getShareConfig');
    
    // 脑图相关路由
    Router::get('/mind-map/root-nodes', 'App\Controller\Api\V1\MindMapController@getRootNodes');
    Router::get('/mind-map/{id}', 'App\Controller\Api\V1\MindMapController@getMindMap');
    
    // 系统相关路由
        Router::get('/system/statistics', 'App\Controller\Api\V1\SystemController@getStatistics', ['middleware' => ['admin']]);
    
        // 配置相关路由
        Router::get('/config/get', 'App\Controller\Api\V1\ConfigController@getConfig');
        Router::post('/config/update', 'App\Controller\Api\V1\ConfigController@updateConfig', ['middleware' => ['admin']]);
        Router::post('/config/batch-update', 'App\Controller\Api\V1\ConfigController@batchUpdateConfig', ['middleware' => ['admin']]);
});
