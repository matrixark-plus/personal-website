<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 管理员认证中间件
 */
class AdminAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @Inject
     * @var JwtAuthMiddleware
     */
    protected $jwtAuthMiddleware;

    public function __construct(ContainerInterface $container, ResponseInterface $response)
    {
        $this->container = $container;
        $this->response = $response;
    }

    /**
     * 处理请求
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        try {
            // 首先通过JWT认证
            $response = $this->jwtAuthMiddleware->process($request, new class implements RequestHandlerInterface {
                public function handle(ServerRequestInterface $request): PsrResponseInterface
                {
                    return $request->getAttribute('response');
                }
            });

            // 检查JWT认证是否失败
            if ($response->getStatusCode() !== 200) {
                return $response;
            }

            // 获取用户信息
            $user = $request->getAttribute('user');
            if (empty($user)) {
                return $this->response->json([
                    'code' => 401,
                    'message' => '用户未认证'
                ])->withStatus(401);
            }

            // 检查用户是否为管理员
            if ($user['role'] !== 'admin') {
                return $this->response->json([
                    'code' => 403,
                    'message' => '需要管理员权限'
                ])->withStatus(403);
            }

            // 设置管理员标识
            $request = $request->withAttribute('is_admin', true);

            // 继续处理请求
            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->response->json([
                'code' => 500,
                'message' => '认证过程中发生错误: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }
}