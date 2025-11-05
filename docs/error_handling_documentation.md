# 错误处理机制文档

本文档详细描述了项目中的错误处理机制，包括异常处理器、中间件以及相关常量类的实现和使用方法。

## 1. 概述

项目实现了完善的错误处理机制，通过异常处理器和中间件的配合，实现了：
- 统一的异常捕获和处理
- 标准化的错误响应格式
- 环境感知的错误信息展示
- 智能的错误日志记录
- 健壮的错误处理流程

## 2. 核心组件

### 2.1 ErrorHandlerMiddleware

错误处理中间件，用于捕获API请求过程中的异常，位于 `app/Middleware/ErrorHandlerMiddleware.php`。

#### 主要功能：
- 捕获所有请求处理过程中的异常
- 根据异常类型确定错误信息和状态码
- 返回结构化的JSON错误响应
- 根据环境决定是否返回详细错误信息
- 智能记录异常日志

#### 关键方法：

| 方法名 | 功能描述 | 参数 | 返回值 |
|-------|---------|-----|-------|
| `process()` | 处理请求并捕获异常 | RequestInterface, HandlerInterface | ResponseInterface |
| `handleException()` | 处理异常并生成响应 | Throwable, RequestInterface | ResponseInterface |
| `getErrorInfo()` | 获取错误信息和状态码 | Throwable | array |
| `logException()` | 记录异常日志 | Throwable, array | void |
| `isDevelopmentEnvironment()` | 判断是否为开发环境 | 无 | bool |
| `validateStatusCode()` | 验证HTTP状态码有效性 | int | bool |
| `createEmergencyResponse()` | 创建紧急响应（当JSON编码失败时） | 无 | ResponseInterface |

### 2.2 AppExceptionHandler

应用异常处理器，用于处理所有应用级异常，位于 `app/Exception/Handler/AppExceptionHandler.php`。

#### 主要功能：
- 处理框架级和应用级异常
- 根据环境返回适当的错误信息
- 记录异常日志

#### 关键方法：

| 方法名 | 功能描述 | 参数 | 返回值 |
|-------|---------|-----|-------|
| `handle()` | 处理异常 | Throwable, ResponseInterface | ResponseInterface |
| `getErrorMessage()` | 根据环境获取错误消息 | Throwable | string |
| `isDevelopmentEnvironment()` | 判断是否为开发环境 | 无 | bool |
| `isValid()` | 验证异常处理器是否有效 | 无 | bool |

## 3. 常量类

### 3.1 StatusCode

定义所有HTTP状态码和业务错误码，位于 `app/Constants/StatusCode.php`。

#### 主要常量：

| 常量名 | 值 | 描述 |
|-------|-----|------|
| `OK` | 200 | 请求成功 |
| `SERVER_ERROR` | 500 | 服务器内部错误 |
| `NOT_FOUND` | 404 | 请求资源不存在 |
| `BAD_REQUEST` | 400 | 请求参数错误 |
| `UNAUTHORIZED` | 401 | 未授权 |
| `FORBIDDEN` | 403 | 禁止访问 |
| `VALIDATION_ERROR` | 422 | 数据验证错误 |

### 3.2 ResponseMessage

定义所有错误响应消息，位于 `app/Constants/ResponseMessage.php`。

#### 主要常量：

| 常量名 | 值 | 描述 |
|-------|-----|------|
| `SERVER_ERROR` | "服务器内部错误" | 服务器错误消息 |
| `NOT_FOUND` | "请求资源不存在" | 404错误消息 |
| `BAD_REQUEST` | "请求参数错误" | 400错误消息 |
| `UNAUTHORIZED` | "未授权访问" | 401错误消息 |
| `FORBIDDEN` | "禁止访问" | 403错误消息 |
| `VALIDATION_ERROR` | "数据验证失败" | 422错误消息 |

## 4. 优化特性

### 4.1 使用常量替代硬编码

所有HTTP状态码和错误消息都通过常量类统一管理，提高代码可维护性。

```php
// 优化前
error_log('500 - 服务器内部错误');

// 优化后
error_log(StatusCode::SERVER_ERROR . ' - ' . ResponseMessage::SERVER_ERROR);
```

### 4.2 统一的环境判断逻辑

两个组件都使用相同的环境判断方法，支持dev/development/local/test环境。

```php
/**
 * 判断是否为开发环境
 * @return bool
 */
protected function isDevelopmentEnvironment(): bool
{
    $env = env('APP_ENV', 'production');
    $devEnvironments = ['dev', 'development', 'local', 'test'];
    return in_array(strtolower($env), $devEnvironments);
}
```

### 4.3 增强的代码健壮性

- **状态码验证**：防止返回无效的HTTP状态码
- **JSON编码失败处理**：提供紧急响应机制
- **异常捕获**：使用try-catch包装关键代码块

### 4.4 智能日志记录

根据异常类型设置不同的日志级别：
- 404/401/403异常：warning级别
- 400/422异常：info级别
- 其他异常：error级别

日志包含请求上下文信息，便于问题排查。

### 4.5 异常类型精确判断

使用instanceof和类名匹配进行异常类型判断，确保准确性。

```php
/**
 * 判断是否为404异常
 * @param Throwable $throwable
 * @return bool
 */
protected function isNotFoundHttpException(Throwable $throwable): bool
{
    return $throwable instanceof NotFoundHttpException || 
           str_contains(get_class($throwable), 'NotFoundHttpException');
}
```

## 5. 响应格式

### 5.1 开发环境响应

```json
{
  "code": 500,
  "message": "服务器内部错误 | Exception: 错误信息 in /path/to/file.php on line 100",
  "data": null,
  "timestamp": 1620000000
}
```

### 5.2 生产环境响应

```json
{
  "code": 500,
  "message": "服务器内部错误",
  "data": null
}
```

## 6. 使用方式

### 6.1 中间件注册

在 `config/autoload/middlewares.php` 中注册错误处理中间件：

```php
return [
    'http' => [
        App\Middleware\ErrorHandlerMiddleware::class,
        // 其他中间件
    ],
];
```

### 6.2 异常抛出

在业务代码中抛出异常：

```php
// 抛出404异常
throw new NotFoundHttpException('资源不存在');

// 抛出验证异常
throw new ValidationException($validator);

// 抛出通用异常
throw new \Exception('业务逻辑错误');
```

## 7. 环境配置

确保正确设置 `APP_ENV` 环境变量：
- 开发环境：dev/development/local/test
- 生产环境：production

## 8. 测试工具

项目包含测试脚本 `test_error_handling.php`，用于验证错误处理机制的正确性。

测试用例包括：
1. 普通异常处理
2. 404异常处理
3. 400异常处理
4. 401异常处理
5. 403异常处理
6. 无效状态码处理
7. 环境判断逻辑测试

## 9. 最佳实践

1. **统一使用常量**：使用StatusCode和ResponseMessage常量类
2. **适当抛出异常**：在业务逻辑错误时主动抛出异常
3. **合理设置环境**：确保开发/生产环境配置正确
4. **关注错误日志**：定期检查错误日志，及时发现问题
5. **保持异常处理一致性**：遵循统一的异常处理模式