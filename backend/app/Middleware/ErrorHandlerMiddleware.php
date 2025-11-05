<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\ResponseMessage;
use App\Constants\StatusCode;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Support\env;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * 统一错误处理中间件
 * 用于捕获和处理API请求过程中的所有异常，返回标准化的错误响应格式
 */
class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(LoggerInterface::class);
    }

    /**
     * 处理请求，捕获可能出现的异常
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            // 正常处理请求
            return $handler->handle($request);
        } catch (Throwable $exception) {
            // 捕获异常并返回统一格式的错误响应
            return $this->handleException($exception, $request);
        }
    }

    /**
     * 处理异常，生成标准化的错误响应
     * @param Throwable $exception 捕获的异常
     * @param ServerRequestInterface $request 请求对象
     * @return ResponseInterface 错误响应
     */
    protected function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        try {
            // 记录错误日志
            $this->logException($exception, $request);

            // 根据异常类型确定错误码和消息
            $errorInfo = $this->getErrorInfo($exception);

            // 创建错误响应并设置HTTP状态码
            $response = $this->container->get(ResponseInterface::class);
            $statusCode = $this->validateStatusCode($errorInfo['code']);
            $response = $response->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');

            // 构建错误响应内容
            $errorResponse = [
                'code' => $statusCode,
                'message' => $errorInfo['message'],
                'data' => null,
            ];

            // 在开发环境中添加timestamp和详细错误信息
            if ($this->isDevelopmentEnvironment()) {
                // 添加时间戳
                $errorResponse['timestamp'] = time();
                
                // 构建详细错误信息
                $errorDetails = [
                    'type' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
                
                // 将详细错误信息追加到消息中
                $errorResponse['message'] .= ' | ' . 
                    $errorDetails['type'] . ': ' . 
                    $errorDetails['message'] . ' in ' . 
                    $errorDetails['file'] . ' on line ' . 
                    $errorDetails['line'];
            }

            // 设置响应体，处理json_encode失败的情况
            $body = json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
            if ($body === false) {
                // 如果json_encode失败，返回一个简单的错误响应
                $simpleResponse = [
                    'code' => StatusCode::INTERNAL_SERVER_ERROR,
                    'message' => ResponseMessage::SERVER_ERROR,
                    'data' => null
                ];
                $body = json_encode($simpleResponse, JSON_UNESCAPED_UNICODE) ?: '{}';
            }
            return $response->withBody(new SwooleStream($body));
        } catch (Throwable $innerException) {
            // 处理异常处理过程中可能出现的异常
            return $this->createEmergencyResponse($innerException);
        }
    }

    /**
     * 获取错误信息
     * @param Throwable $exception 异常对象
     * @return array 错误信息数组
     */
    protected function getErrorInfo(Throwable $exception): array
    {
        // 检查是否是HTTP异常
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
            $statusCode = $this->validateStatusCode($statusCode);
            return [
                'code' => $statusCode,
                'message' => $exception->getMessage() ?: ResponseMessage::getDefaultMessage($statusCode),
            ];
        }

        // 根据异常类型返回对应的错误码和消息
        if ($this->isNotFoundHttpException($exception)) {
            return [
                'code' => StatusCode::NOT_FOUND,
                'message' => ResponseMessage::RESOURCE_NOT_FOUND,
            ];
        }

        if ($this->isUnauthorizedHttpException($exception)) {
            return [
                'code' => StatusCode::UNAUTHORIZED,
                'message' => ResponseMessage::UNAUTHORIZED,
            ];
        }

        if ($this->isForbiddenHttpException($exception)) {
            return [
                'code' => StatusCode::FORBIDDEN,
                'message' => ResponseMessage::NO_PERMISSION,
            ];
        }

        if ($this->isBadRequestHttpException($exception)) {
            return [
                'code' => StatusCode::BAD_REQUEST,
                'message' => ResponseMessage::PARAM_ERROR,
            ];
        }

        if ($this->isValidationException($exception)) {
            return [
                'code' => StatusCode::VALIDATION_ERROR,
                'message' => ResponseMessage::PARAM_ERROR,
            ];
        }

        // 其他所有异常默认为服务器内部错误
        $defaultMessage = $this->isDevelopmentEnvironment() ? $exception->getMessage() : ResponseMessage::SERVER_ERROR;
        return [
            'code' => StatusCode::INTERNAL_SERVER_ERROR,
            'message' => $defaultMessage,
        ];
    }

    /**
     * 记录异常日志
     * @param Throwable $exception 异常对象
     * @param ServerRequestInterface $request 请求对象
     */
    protected function logException(Throwable $exception, ServerRequestInterface $request): void
    {
        $uri = (string) $request->getUri();
        $method = $request->getMethod();
        $clientIp = $request->getServerParams()['remote_addr'] ?? 'unknown';
        
        // 构建日志消息
        $logMessage = sprintf(
            'API Error: %s %s - IP: %s - %s: %s',
            $method,
            $uri,
            $clientIp,
            get_class($exception),
            $exception->getMessage()
        );

        // 根据异常类型确定日志级别
        if ($this->isNotFoundHttpException($exception) || 
            $this->isUnauthorizedHttpException($exception) || 
            $this->isForbiddenHttpException($exception)) {
            $this->logger->warning($logMessage, ['exception' => $exception]);
        } elseif ($this->isBadRequestHttpException($exception) || 
                 $this->isValidationException($exception)) {
            $this->logger->info($logMessage, ['exception' => $exception]);
        } else {
            // 其他异常记录为错误级别
            $this->logger->error($logMessage, ['exception' => $exception]);
        }
    }

    /**
     * 判断是否为开发环境
     * @return bool 是否为开发环境
     */
    protected function isDevelopmentEnvironment(): bool
    {
        $env = env('APP_ENV', 'production');
        return in_array($env, ['dev', 'development', 'local', 'test']);
    }

    /**
     * 验证HTTP状态码的有效性
     * @param int $statusCode HTTP状态码
     * @return int 有效的HTTP状态码
     */
    protected function validateStatusCode(int $statusCode): int
    {
        // 检查状态码是否在有效范围内
        if ($statusCode >= 100 && $statusCode < 600) {
            return $statusCode;
        }
        // 返回默认的服务器错误状态码
        return StatusCode::INTERNAL_SERVER_ERROR;
    }

    /**
     * 创建紧急响应
     * @param Throwable $exception 异常对象
     * @return ResponseInterface 紧急响应
     */
    protected function createEmergencyResponse(Throwable $exception): ResponseInterface
    {
        try {
            $response = $this->container->get(ResponseInterface::class);
            $response = $response->withStatus(StatusCode::INTERNAL_SERVER_ERROR)
                ->withHeader('Content-Type', 'application/json');
            
            $emergencyBody = json_encode([
                'code' => StatusCode::INTERNAL_SERVER_ERROR,
                'message' => ResponseMessage::SERVER_ERROR
            ], JSON_UNESCAPED_UNICODE) ?: '{}';
            
            return $response->withBody(new SwooleStream($emergencyBody));
        } catch (Throwable $e) {
            // 如果所有都失败，尝试创建一个最基本的响应
            $response = new \Hyperf\HttpMessage\Response\Response();
            $response = $response->withStatus(StatusCode::INTERNAL_SERVER_ERROR);
            $response = $response->withHeader('Content-Type', 'application/json');
            return $response->withBody(new SwooleStream('{"code":500,"message":"Internal Server Error"}'));
        }
    }

    /**
     * 判断是否为NotFoundHttpException异常
     * @param Throwable $exception 异常对象
     * @return bool 是否为NotFoundHttpException异常
     */
    protected function isNotFoundHttpException(Throwable $exception): bool
    {
        $className = get_class($exception);
        return $className === 'Symfony\Component\HttpKernel\Exception\NotFoundHttpException' ||
               strpos($className, 'NotFoundHttpException') !== false;
    }

    /**
     * 判断是否为UnauthorizedHttpException异常
     * @param Throwable $exception 异常对象
     * @return bool 是否为UnauthorizedHttpException异常
     */
    protected function isUnauthorizedHttpException(Throwable $exception): bool
    {
        $className = get_class($exception);
        return $className === 'Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException' ||
               strpos($className, 'UnauthorizedHttpException') !== false;
    }

    /**
     * 判断是否为ForbiddenHttpException异常
     * @param Throwable $exception 异常对象
     * @return bool 是否为ForbiddenHttpException异常
     */
    protected function isForbiddenHttpException(Throwable $exception): bool
    {
        $className = get_class($exception);
        return $className === 'Symfony\Component\HttpKernel\Exception\ForbiddenHttpException' ||
               strpos($className, 'ForbiddenHttpException') !== false;
    }

    /**
     * 判断是否为BadRequestHttpException异常
     * @param Throwable $exception 异常对象
     * @return bool 是否为BadRequestHttpException异常
     */
    protected function isBadRequestHttpException(Throwable $exception): bool
    {
        $className = get_class($exception);
        return $className === 'Symfony\Component\HttpKernel\Exception\BadRequestHttpException' ||
               strpos($className, 'BadRequestHttpException') !== false;
    }

    /**
     * 判断是否为ValidationException异常
     * @param Throwable $exception 异常对象
     * @return bool 是否为ValidationException异常
     */
    protected function isValidationException(Throwable $exception): bool
    {
        $className = get_class($exception);
        return $className === 'Hyperf\Validation\ValidationException' ||
               strpos($className, 'ValidationException') !== false;
    }
}