<?php

declare(strict_types=1);
/**
 * 作品控制器
 */
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * @AutoController(prefix="/api/portfolio")
 */
class PortfolioController extends AbstractController
{
    /**
     * 获取精选作品
     * @GetMapping("/items")
     */
    public function items()
    {
        // 获取limit参数，默认为5
        $limit = $this->request->input('limit', 5);
        $limit = min(intval($limit), 20); // 限制最大数量为20
        
        // 模拟数据，实际应用中应该从数据库获取
        $mockItems = [
            [
                'id' => 1,
                'title' => '智能推荐系统',
                'description' => '基于机器学习的个性化内容推荐系统，支持多种算法和实时推荐。',
                'image' => '/images/portfolio1.jpg',
                'category' => 'AI应用',
                'techStack' => ['Python', 'TensorFlow', 'Redis'],
                'url' => 'https://example.com/project1'
            ],
            [
                'id' => 2,
                'title' => '电商平台重构',
                'description' => '使用微服务架构重构的电商平台，支持高并发和分布式部署。',
                'image' => '/images/portfolio2.jpg',
                'category' => 'Web应用',
                'techStack' => ['Java', 'Spring Cloud', 'Docker', 'Kubernetes'],
                'url' => 'https://example.com/project2'
            ],
            [
                'id' => 3,
                'title' => '实时协作编辑工具',
                'description' => '类似Google Docs的实时多人协作编辑系统，支持冲突解决。',
                'image' => '/images/portfolio3.jpg',
                'category' => '协作工具',
                'techStack' => ['Node.js', 'Socket.IO', 'MongoDB'],
                'url' => 'https://example.com/project3'
            ],
            [
                'id' => 4,
                'title' => '数据分析仪表盘',
                'description' => '企业级数据可视化和分析平台，支持多种图表和自定义报表。',
                'image' => '/images/portfolio4.jpg',
                'category' => '数据应用',
                'techStack' => ['React', 'TypeScript', 'ECharts', 'Node.js'],
                'url' => 'https://example.com/project4'
            ],
            [
                'id' => 5,
                'title' => '移动支付SDK',
                'description' => '跨平台移动支付解决方案，支持多种支付方式和安全验证。',
                'image' => '/images/portfolio5.jpg',
                'category' => '移动开发',
                'techStack' => ['Flutter', 'Dart', 'gRPC'],
                'url' => 'https://example.com/project5'
            ]
        ];
        
        // 根据limit参数截取数据
        $items = array_slice($mockItems, 0, $limit);
        
        return [
            'code' => 0,
            'message' => 'success',
            'data' => $items
        ];
    }
}