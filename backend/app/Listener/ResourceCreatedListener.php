<?php

declare(strict_types=1);

namespace App\Listener;

use App\Service\BloomFilterService;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;

/**
 * 资源创建事件监听器
 * 监听资源创建事件，自动将资源ID添加到布隆过滤器
 */
class ResourceCreatedListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var BloomFilterService
     */
    protected $bloomFilterService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->bloomFilterService = $container->get(BloomFilterService::class);
    }

    /**
     * 返回需要监听的事件列表
     */
    public function listen(): array
    {
        // 这里可以添加实际的资源创建事件
        // 由于项目中可能没有定义具体的事件类，我们暂时返回一个空数组
        // 实际使用时，应该返回项目中定义的资源创建事件类
        return [];
    }

    /**
     * 处理事件
     * @param object $event 事件对象
     */
    public function process(object $event): void
    {
        // 这里处理事件，将资源ID添加到布隆过滤器
        // 根据实际的事件对象结构，提取资源类型和ID
        // 例如：
        // $resourceType = $event->getResourceType();
        // $resourceId = $event->getResourceId();
        // $this->bloomFilterService->addResource($resourceType, $resourceId);
    }

    /**
     * 手动更新布隆过滤器
     * 供没有使用事件机制的地方调用
     * @param string $resourceType 资源类型
     * @param mixed $resourceId 资源ID
     */
    public static function updateBloomFilter(string $resourceType, $resourceId): void
    {
        $container = ApplicationContext::getContainer();
        $bloomFilterService = $container->get(BloomFilterService::class);
        $bloomFilterService->addResource($resourceType, $resourceId);
    }
}