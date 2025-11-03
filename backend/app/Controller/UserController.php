<?php

declare(strict_types=1);
/**
 * 用户控制器
 */
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * @AutoController(prefix="/api/user")
 */
class UserController extends AbstractController
{
    /**
     * 获取用户基本信息
     * @GetMapping("/profile")
     */
    public function profile()
    {
        // 模拟数据，实际应用中应该从数据库获取
        $userInfo = [
            'name' => '张三',
            'avatar' => '/images/avatar.jpg',
            'bio' => '热爱技术的开发者，专注于全栈开发和人工智能领域。',
            'title' => '全栈工程师',
            'location' => '北京',
            'socialLinks' => [
                'github' => 'https://github.com',
                'twitter' => 'https://twitter.com',
                'linkedin' => 'https://linkedin.com'
            ]
        ];
        
        return [
            'code' => 0,
            'message' => 'success',
            'data' => $userInfo
        ];
    }
}