<?php

declare(strict_types=1);
/**
 * 博客控制器
 */
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * @AutoController(prefix="/api/blog")
 */
class BlogController extends AbstractController
{
    /**
     * 获取最新博客文章
     * @GetMapping("/posts")
     */
    public function posts()
    {
        // 获取limit参数，默认为5
        $limit = $this->request->input('limit', 5);
        $limit = min(intval($limit), 20); // 限制最大数量为20
        
        // 模拟数据，实际应用中应该从数据库获取
        $mockPosts = [
            [
                'id' => 1,
                'title' => 'Next.js 14 新特性详解',
                'summary' => '探索Next.js 14带来的全新功能和性能优化，包括服务器组件和数据获取策略。',
                'coverImage' => '/images/blog1.jpg',
                'publishDate' => '2024-01-15',
                'category' => '前端开发',
                'viewCount' => 1234
            ],
            [
                'id' => 2,
                'title' => 'Hyperf框架性能调优实践',
                'summary' => '分享在生产环境中使用Hyperf框架时的性能优化技巧和最佳实践。',
                'coverImage' => '/images/blog2.jpg',
                'publishDate' => '2024-01-10',
                'category' => '后端开发',
                'viewCount' => 987
            ],
            [
                'id' => 3,
                'title' => '微服务架构下的分布式事务处理',
                'summary' => '深入探讨微服务架构中分布式事务的各种解决方案及其适用场景。',
                'coverImage' => '/images/blog3.jpg',
                'publishDate' => '2024-01-05',
                'category' => '架构设计',
                'viewCount' => 2341
            ],
            [
                'id' => 4,
                'title' => '使用TypeScript提升代码质量',
                'summary' => '如何利用TypeScript的静态类型检查提高项目代码质量和开发效率。',
                'coverImage' => '/images/blog4.jpg',
                'publishDate' => '2024-01-01',
                'category' => '前端开发',
                'viewCount' => 876
            ],
            [
                'id' => 5,
                'title' => 'Docker容器化实践指南',
                'summary' => '从零开始学习Docker容器化技术，包括基础概念和实战部署案例。',
                'coverImage' => '/images/blog5.jpg',
                'publishDate' => '2023-12-28',
                'category' => 'DevOps',
                'viewCount' => 1567
            ]
        ];
        
        // 根据limit参数截取数据
        $posts = array_slice($mockPosts, 0, $limit);
        
        return [
            'code' => 0,
            'message' => 'success',
            'data' => $posts
        ];
    }
}