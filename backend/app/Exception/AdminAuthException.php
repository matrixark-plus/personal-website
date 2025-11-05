<?php

namespace App\Exception;

use Hyperf\Server\Exception\ServerException;

/**
 * 管理员认证异常
 * 用于处理管理员认证相关的异常情况
 */
class AdminAuthException extends ServerException
{
    /**
     * @param string $message 错误消息
     * @param int $code 错误码
     * @param \Throwable|null $previous 之前的异常
     */
    public function __construct(string $message = '管理员认证失败', int $code = 401, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}