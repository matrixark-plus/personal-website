<?php

declare(strict_types=1);

namespace App\Service;

use App\Middleware\BloomFilterMiddleware;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;

/**
 * 布隆过滤器服务
 * 用于在资源创建、更新时维护布隆过滤器数据
 */
class BloomFilterService
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var BloomFilterMiddleware
     */
    protected $middleware;

    /**
     * 过滤器名称映射
     * @var array
     */
    protected $filterMap = [
        'blog' => 'bloom_filter:blog',
        'article' => 'bloom_filter:blog',
        'work' => 'bloom_filter:work',
        'mind_map' => 'bloom_filter:mind_map',
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        // 获取中间件实例
        $this->middleware = $container->get(BloomFilterMiddleware::class);
    }

    /**
     * 添加资源到布隆过滤器
     * @param string $resourceType 资源类型
     * @param string|int $resourceId 资源ID
     */
    public function addResource(string $resourceType, $resourceId): void
    {
        $filterName = $this->getFilterName($resourceType);
        $this->middleware->addToFilter($filterName, (string)$resourceId);
    }

    /**
     * 批量添加资源到布隆过滤器
     * @param string $resourceType 资源类型
     * @param array $resourceIds 资源ID数组
     */
    public function addResources(string $resourceType, array $resourceIds): void
    {
        $filterName = $this->getFilterName($resourceType);
        foreach ($resourceIds as $id) {
            $this->middleware->addToFilter($filterName, (string)$id);
        }
    }

    /**
     * 获取过滤器名称
     * @param string $resourceType 资源类型
     * @return string 过滤器名称
     */
    protected function getFilterName(string $resourceType): string
    {
        $type = strtolower($resourceType);
        return $this->filterMap[$type] ?? 'bloom_filter:default';
    }
}