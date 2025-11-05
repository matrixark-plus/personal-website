<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 布隆过滤器中间件
 * 用于快速过滤不存在的资源请求，减少后端处理压力
 * 基于Redis实现，使用Redis的bit操作来存储布隆过滤器数据
 */
class BloomFilterMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * 布隆过滤器的key前缀
     * @var string
     */
    protected $prefix = 'bloom_filter:';

    /**
     * 布隆过滤器的大小（位数组的长度）
     * @var int
     */
    protected $size = 1000000;

    /**
     * 哈希函数的数量
     * @var int
     */
    protected $hashCount = 7;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        // 获取Redis实例
        $this->redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get('default');
    }

    /**
     * 处理请求
     * 对于GET请求，检查资源是否可能存在
     * 对于资源不存在的情况，直接返回404，避免后端数据库查询
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 只对GET请求进行布隆过滤器检查
        if ($request->getMethod() !== 'GET') {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();
        
        // 检查是否是需要过滤的资源路径
        // 例如：博客详情、作品详情等可能大量不存在的资源请求
        if ($this->shouldFilter($path)) {
            $resourceKey = $this->extractResourceKey($path);
            $filterName = $this->getFilterName($path);

            // 如果资源不在布隆过滤器中，直接返回404
            if (!$this->mightExist($filterName, $resourceKey)) {
                return $this->createNotFoundResponse();
            }
        }

        return $handler->handle($request);
    }

    /**
     * 检查路径是否需要进行布隆过滤器过滤
     * @param string $path 请求路径
     * @return bool 是否需要过滤
     */
    protected function shouldFilter(string $path): bool
    {
        // 可以在这里添加需要过滤的路径规则
        // 例如：文章详情、作品详情、用户详情等
        $patterns = [
            '/blog/[0-9]+',
            '/article/[0-9]+',
            '/work/[0-9]+',
            '/mind-map/[0-9]+',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match('#^' . str_replace('/', '\\/', $pattern) . '$#', $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 从路径中提取资源键
     * @param string $path 请求路径
     * @return string 资源键
     */
    protected function extractResourceKey(string $path): string
    {
        // 提取路径中的ID部分作为资源键
        $parts = explode('/', $path);
        return end($parts) ?: '';
    }

    /**
     * 获取布隆过滤器的名称
     * @param string $path 请求路径
     * @return string 过滤器名称
     */
    protected function getFilterName(string $path): string
    {
        // 根据路径确定过滤器类型
        if (strpos($path, '/blog/') !== false || strpos($path, '/article/') !== false) {
            return $this->prefix . 'blog';
        }
        if (strpos($path, '/work/') !== false) {
            return $this->prefix . 'work';
        }
        if (strpos($path, '/mind-map/') !== false) {
            return $this->prefix . 'mind_map';
        }
        return $this->prefix . 'default';
    }

    /**
     * 检查资源是否可能存在（布隆过滤器查询）
     * @param string $filterName 过滤器名称
     * @param string $key 资源键
     * @return bool 是否可能存在
     */
    protected function mightExist(string $filterName, string $key): bool
    {
        if (empty($key)) {
            return true;
        }

        $bitPositions = $this->getBitPositions($key);
        
        // 检查所有位是否都为1
        foreach ($bitPositions as $position) {
            if (!$this->redis->getBit($filterName, $position)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 添加资源到布隆过滤器
     * @param string $filterName 过滤器名称
     * @param string $key 资源键
     */
    public function addToFilter(string $filterName, string $key): void
    {
        $bitPositions = $this->getBitPositions($key);
        
        // 设置所有位为1
        foreach ($bitPositions as $position) {
            $this->redis->setBit($filterName, $position, 1);
        }
    }

    /**
     * 生成布隆过滤器的多个哈希位置
     * @param string $key 资源键
     * @return array 位位置数组
     */
    protected function getBitPositions(string $key): array
    {
        $positions = [];
        
        // 使用多个哈希函数生成不同的位位置
        for ($i = 0; $i < $this->hashCount; $i++) {
            // 使用不同的种子生成多个哈希值
            $hash = crc32($key . $i);
            $position = abs($hash % $this->size);
            $positions[] = $position;
        }
        
        return $positions;
    }

    /**
     * 创建404响应
     * @return ResponseInterface 404响应
     */
    protected function createNotFoundResponse(): ResponseInterface
    {
        $responseFactory = $this->container->get(ResponseInterface::class);
        
        return $responseFactory->withStatus(404)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(
                $responseFactory->stream(
                    json_encode([
                        'code' => 404,
                        'message' => 'Resource not found',
                        'data' => null
                    ], JSON_UNESCAPED_UNICODE)
                )
            );
    }
}