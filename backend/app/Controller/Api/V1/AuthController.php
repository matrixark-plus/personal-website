<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Controller\AbstractController;
use App\Model\User;
use App\Service\AuthService;
use App\Service\MailService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use App\Middleware\JwtAuthMiddleware;

/**
 * 用户认证控制器
 * @Controller(prefix="/api/v1/auth")
 */
class AuthController extends AbstractController
{
    /**
     * @Inject
     * @var AuthService
     */
    protected $authService;

    /**
     * @Inject
     * @var MailService
     */
    protected $mailService;

    /**
     * 用户注册
     * @RequestMapping(path="/register", methods={"POST"})
     */
    public function register(RequestInterface $request)
    {
        try {
            $data = $request->all();
            
            // 验证请求参数
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                return $this->fail(400, '用户名、邮箱和密码不能为空');
            }
            
            // 验证邮箱格式
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->fail(400, '邮箱格式不正确');
            }
            
            // 验证密码长度
            if (strlen($data['password']) < 6) {
                return $this->fail(400, '密码长度不能少于6位');
            }
            
            // 注册用户
            $result = $this->authService->register($data);
            
            if ($result['success']) {
                // 注册成功，发送欢迎邮件
                $this->mailService->sendWelcomeEmail($result['user']->email, [
                    'username' => $result['user']->username,
                ]);
                
                return $this->success([
                    'user' => $result['user'],
                    'token' => $result['token']
                ], '注册成功');
            } else {
                return $this->fail(400, $result['message']);
            }
        } catch (\Exception $e) {
            logger()->error('注册异常: ' . $e->getMessage());
            return $this->fail(500, '注册失败: ' . $e->getMessage());
        }
    }

    /**
     * 用户登录
     * @RequestMapping(path="/login", methods={"POST"})
     */
    public function login(RequestInterface $request)
    {
        try {
            $data = $request->all();
            
            // 验证请求参数
            if (empty($data['email']) || empty($data['password'])) {
                return $this->fail(400, '邮箱和密码不能为空');
            }
            
            // 登录验证
            $result = $this->authService->login($data['email'], $data['password']);
            
            if ($result['success']) {
                return $this->success([
                    'user' => $result['user'],
                    'token' => $result['token'],
                    'expires_in' => config('auth.guards.jwt.ttl', 7200)
                ], '登录成功');
            } else {
                return $this->fail(401, $result['message']);
            }
        } catch (\Exception $e) {
            logger()->error('登录异常: ' . $e->getMessage());
            return $this->fail(500, '登录失败: ' . $e->getMessage());
        }
    }

    /**
     * 用户登出
     * @RequestMapping(path="/logout", methods={"POST"})
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function logout(RequestInterface $request)
    {
        try {
            $result = $this->authService->logout();
            
            if ($result) {
                return $this->success(null, '登出成功');
            } else {
                return $this->fail(500, '登出失败');
            }
        } catch (\Exception $e) {
            logger()->error('登出异常: ' . $e->getMessage());
            return $this->fail(500, '登出失败: ' . $e->getMessage());
        }
    }

    /**
     * 刷新Token
     * @RequestMapping(path="/refresh", methods={"POST"})
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function refresh(RequestInterface $request)
    {
        try {
            $result = $this->authService->refreshToken();
            
            if ($result['success']) {
                return $this->success([
                    'token' => $result['token'],
                    'expires_in' => config('auth.guards.jwt.ttl', 7200)
                ], 'Token刷新成功');
            } else {
                return $this->fail(401, $result['message']);
            }
        } catch (\Exception $e) {
            logger()->error('刷新Token异常: ' . $e->getMessage());
            return $this->fail(500, 'Token刷新失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取当前用户信息
     * @RequestMapping(path="/me", methods={"GET"})
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function getUserInfo(RequestInterface $request)
    {
        try {
            $user = $this->authService->getCurrentUser();
            
            if ($user) {
                return $this->success($user, '获取用户信息成功');
            } else {
                return $this->fail(401, '用户未登录');
            }
        } catch (\Exception $e) {
            logger()->error('获取用户信息异常: ' . $e->getMessage());
            return $this->fail(500, '获取用户信息失败: ' . $e->getMessage());
        }
    }

    /**
     * 修改密码
     * @RequestMapping(path="/change-password", methods={"POST"})
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function changePassword(RequestInterface $request)
    {
        try {
            $data = $request->all();
            
            // 验证请求参数
            if (empty($data['old_password']) || empty($data['new_password'])) {
                return $this->fail(400, '旧密码和新密码不能为空');
            }
            
            // 验证新密码长度
            if (strlen($data['new_password']) < 6) {
                return $this->fail(400, '新密码长度不能少于6位');
            }
            
            // 修改密码
            $result = $this->authService->changePassword($data['old_password'], $data['new_password']);
            
            if ($result['success']) {
                return $this->success(null, '密码修改成功');
            } else {
                return $this->fail(400, $result['message']);
            }
        } catch (\Exception $e) {
            logger()->error('修改密码异常: ' . $e->getMessage());
            return $this->fail(500, '密码修改失败: ' . $e->getMessage());
        }
    }
}