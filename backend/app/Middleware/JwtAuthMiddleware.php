<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Auth\AuthException;
use Hyperf\Auth\AuthManager;
use Hyperf\Auth\Exception\UnauthorizedException;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * JWT认证中间件 - 基于角色名和路径的RBAC权限控制系统
 * 支持游客、会员、管理员等多种角色的权限控制
 * 通过路由路径匹配实现灵活的访问控制策略
 */
class JwtAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @var AuthManager
     */
    protected $auth;

    /**
     * RBAC权限配置 - 基于路由路径的角色访问控制
     * 格式: ['/api/admin/*' => ['admin'], '/api/user/*' => ['user', 'admin']]
     * 空数组表示允许所有已认证用户访问
     * @var array
     */
    protected $routeRoles = [];

    /**
     * JwtAuthMiddleware constructor.
     * @param ContainerInterface $container
     * @param HttpResponse $response
     * @param array $options 配置选项
     *                      - ['route_roles' => ['/api/admin/*' => ['admin'], '/api/user/*' => ['user', 'admin']]]
     */
    public function __construct(ContainerInterface $container, HttpResponse $response, array $options = [])
    {
        $this->container = $container;
        $this->response = $response;
        $this->auth = ApplicationContext::getContainer()->get(AuthManager::class);
        
        // 从选项中设置基于路由的角色配置
        if (isset($options['route_roles']) && is_array($options['route_roles'])) {
            $this->routeRoles = $options['route_roles'];
        }
    }
    
    /**
     * 检查路由是否匹配模式
     * 支持通配符匹配，如: /api/admin/* 匹配所有以/api/admin/开头的路由
     * @param string $route 当前请求路径
     * @param string $pattern 路由模式
     * @return bool 是否匹配
     */
    protected function matchRoute(string $route, string $pattern): bool
    {
        // 精确匹配
        if ($route === $pattern) {
            return true;
        }
        
        // 通配符匹配
        if (strpos($pattern, '*') !== false) {
            $regex = str_replace('*', '.*', preg_quote($pattern, '/'));
            return (bool)preg_match("/^{$regex}$/", $route);
        }
        
        return false;
    }
    
    /**
     * 根据请求路径获取所需的角色列表
     * @param string $path 请求路径
     * @return array 所需角色列表
     */
    protected function getRequiredRolesByPath(string $path): array
    {
        // 默认权限规则：
        // 1. 管理接口默认需要admin角色
        if (strpos($path, '/api/admin/') === 0) {
            return ['admin'];
        }
        
        // 2. 用户相关接口默认需要user或admin角色
        if (strpos($path, '/api/user/') === 0) {
            return ['user', 'admin'];
        }
        
        // 3. 检查自定义路由配置
        foreach ($this->routeRoles as $pattern => $roles) {
            if ($this->matchRoute($path, $pattern)) {
                return $roles;
            }
        }
        
        // 默认允许所有已认证用户访问（空数组表示无角色限制）
        return [];
    }

    /**
     * 处理认证和权限检查
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            // 获取JWT认证实例并验证token
            $guard = $this->auth->guard('jwt');
            $user = $guard->user();

            if (!$user) {
                throw new UnauthorizedException('未授权访问');
            }

            // 将用户信息转换为数组并存储到request属性中
            $userArray = is_object($user) ? (array)$user : $user;
            $request = $request->withAttribute('user', $userArray);
            
            // 获取用户角色
            $userRole = $userArray['role'] ?? 'guest'; // 默认角色为游客
            
            // 管理员角色完全跳过角色和路径检查，拥有最高权限
            if ($userRole === 'admin') {
                // 设置请求属性
                $request = $request->withAttribute('user_role', $userRole);
                $request = $request->withAttribute('is_admin', true);
                $request = $request->withAttribute('required_roles', []); // 空数组表示无限制
                
                return $handler->handle($request);
            }
            
            // 非管理员角色进行路径检查
            // 获取请求路径
            $path = $request->getUri()->getPath();
            
            // 获取当前路径所需的角色
            $requiredRoles = $this->getRequiredRolesByPath($path);
            
            // 进行权限检查
            // 如果需要特定角色，且用户角色不在允许列表中
            // 注意：空数组表示允许所有已认证用户
            if (!empty($requiredRoles) && !in_array($userRole, $requiredRoles)) {
                return $this->response->json([
                    'code' => 403,
                    'message' => '权限不足，需要以下角色之一：' . implode(', ', $requiredRoles),
                    'data' => null,
                ])->withStatus(403);
            }
            
            // 设置请求属性
            $request = $request->withAttribute('user_role', $userRole);
            $request = $request->withAttribute('required_roles', $requiredRoles);
            
            return $handler->handle($request);
        } catch (AuthException $exception) {
            // JWT认证失败 - 返回401未授权
            return $this->response->json([
                'code' => 401,
                'message' => '未授权访问',
                'data' => null,
            ])->withStatus(401);
        } catch (\Throwable $exception) {
            // 其他错误 - 返回500服务器错误
            return $this->response->json([
                'code' => 500,
                'message' => '认证过程中发生错误',
                'data' => null,
            ])->withStatus(500);
        }
    }
}