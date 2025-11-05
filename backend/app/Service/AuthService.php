<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\User;
use Hyperf\Auth\AuthManager;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Str;
use Hyperf\DbConnection\Db;

class AuthService
{
    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

    /**
     * 用户注册
     * @param array $data 注册数据
     * @return array
     */
    public function register(array $data): array
    {
        // 检查用户名是否已存在
        if (User::where('username', $data['username'])->exists()) {
            return [
                'success' => false,
                'message' => '用户名已存在'
            ];
        }

        // 检查邮箱是否已存在
        if (User::where('email', $data['email'])->exists()) {
            return [
                'success' => false,
                'message' => '邮箱已被注册'
            ];
        }

        // 开始事务
        return Db::transaction(function () use ($data) {
            // 创建用户
            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
                'nickname' => $data['username'],
                'avatar' => 'https://via.placeholder.com/150',
                'status' => User::STATUS_ACTIVE,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            // 生成JWT token
            $token = $this->generateToken($user);

            // 记录注册日志
            logger()->info('用户注册成功: ' . $user->id . ' - ' . $user->username);

            return [
                'success' => true,
                'user' => $user,
                'token' => $token
            ];
        });
    }

    /**
     * 用户登录
     * @param string $email 邮箱
     * @param string $password 密码
     * @return array
     */
    public function login(string $email, string $password): array
    {
        // 查找用户
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => '邮箱或密码错误'
            ];
        }

        // 检查用户状态
        if ($user->status !== User::STATUS_ACTIVE) {
            return [
                'success' => false,
                'message' => '账户已被禁用'
            ];
        }

        // 验证密码
        if (!password_verify($password, $user->password_hash)) {
            return [
                'success' => false,
                'message' => '邮箱或密码错误'
            ];
        }

        // 生成JWT token
        $token = $this->generateToken($user);

        // 更新登录信息
        $user->last_login_at = time();
        $user->last_login_ip = context()->get('remote_addr', '0.0.0.0');
        $user->login_count = $user->login_count + 1;
        $user->save();

        // 记录登录日志
        logger()->info('用户登录成功: ' . $user->id . ' - ' . $user->username);

        return [
            'success' => true,
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * 用户登出
     * @return bool
     */
    public function logout(): bool
    {
        try {
            $guard = $this->auth->guard('jwt');
            $guard->logout();
            return true;
        } catch (\Exception $e) {
            logger()->error('用户登出失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 刷新Token
     * @return array
     */
    public function refreshToken(): array
    {
        try {
            $guard = $this->auth->guard('jwt');
            $token = $guard->refresh();
            
            return [
                'success' => true,
                'token' => $token
            ];
        } catch (\Exception $e) {
            logger()->error('刷新Token失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Token已过期，请重新登录'
            ];
        }
    }

    /**
     * 获取当前登录用户
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        try {
            $guard = $this->auth->guard('jwt');
            return $guard->user();
        } catch (\Exception $e) {
            logger()->error('获取当前用户失败: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 修改密码
     * @param string $oldPassword 旧密码
     * @param string $newPassword 新密码
     * @return array
     */
    public function changePassword(string $oldPassword, string $newPassword): array
    {
        // 获取当前用户
        $user = $this->getCurrentUser();
        if (!$user) {
            return [
                'success' => false,
                'message' => '用户未登录'
            ];
        }

        // 验证旧密码
        if (!password_verify($oldPassword, $user->password_hash)) {
            return [
                'success' => false,
                'message' => '旧密码错误'
            ];
        }

        // 检查新旧密码是否相同
        if (password_verify($newPassword, $user->password_hash)) {
            return [
                'success' => false,
                'message' => '新密码不能与旧密码相同'
            ];
        }

        // 更新密码
        $user->password_hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $user->password_updated_at = time();
        $user->save();

        // 记录日志
        logger()->info('用户修改密码成功: ' . $user->id . ' - ' . $user->username);

        return [
            'success' => true,
            'message' => '密码修改成功'
        ];
    }

    /**
     * 生成JWT token
     * @param User $user 用户模型
     * @return string
     */
    protected function generateToken(User $user): string
    {
        $guard = $this->auth->guard('jwt');
        return $guard->login($user);
    }

    /**
     * 验证用户权限
     * @param User $user 用户模型
     * @param string $permission 权限名称
     * @return bool
     */
    public function checkPermission(User $user, string $permission): bool
    {
        // 超级管理员拥有所有权限
        if ($user->is_admin) {
            return true;
        }

        // 这里可以根据实际需求实现更复杂的权限检查逻辑
        // 例如从数据库中查询用户角色和权限
        
        return false;
    }

    /**
     * 重置密码（发送重置链接）
     * @param string $email 邮箱
     * @return array
     */
    public function sendResetPasswordEmail(string $email): array
    {
        // 查找用户
        $user = User::where('email', $email)->first();
        if (!$user) {
            // 出于安全考虑，即使邮箱不存在也返回成功消息
            return [
                'success' => true,
                'message' => '重置密码链接已发送到您的邮箱'
            ];
        }

        // 生成重置令牌
        $token = Str::random(60);
        $expires = time() + 3600; // 1小时后过期

        // 这里应该将重置令牌保存到数据库中
        // 为了简化，这里只是记录日志
        logger()->info('发送重置密码邮件: ' . $user->id . ' - ' . $user->email . ' - ' . $token);

        // 发送重置密码邮件
        // 实际项目中应该调用邮件服务发送邮件

        return [
            'success' => true,
            'message' => '重置密码链接已发送到您的邮箱'
        ];
    }
}