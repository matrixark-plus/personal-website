# 中间件优化方案

本文档描述了项目中实现的中间件优化方案，包括错误处理中间件、布隆过滤器和速率限制的实现与使用方法。

## 1. 布隆过滤器

### 1.1 概述

布隆过滤器是一种空间效率高的概率性数据结构，用于快速判断一个元素是否在集合中。它可能会返回误判（即可能将不在集合中的元素误判为在集合中），但不会漏判（即不会将在集合中的元素误判为不在集合中）。

在本项目中，布隆过滤器用于快速过滤不存在的资源请求，减少后端数据库查询压力。

### 1.2 实现原理

布隆过滤器基于Redis的位操作实现：

1. 使用一个位数组来表示布隆过滤器
2. 通过多个哈希函数将同一个元素映射到位数组的不同位置
3. 检查元素是否存在时，检查所有映射位置是否都为1

### 1.3 核心组件

#### 1.3.1 BloomFilterMiddleware

全局中间件，自动拦截并检查GET请求：

```php
// 位于：app/Middleware/BloomFilterMiddleware.php
```

主要功能：
- 自动拦截符合规则的GET请求（如博客详情、作品详情等）
- 使用布隆过滤器快速判断资源是否可能存在
- 对不存在的资源直接返回404，避免数据库查询

#### 1.3.2 BloomFilterService

服务类，提供布隆过滤器管理功能：

```php
// 位于：app/Service/BloomFilterService.php
```

主要方法：
- `addResource($resourceType, $resourceId)` - 添加单个资源到过滤器
- `addResources($resourceType, $resourceIds)` - 批量添加资源到过滤器

### 1.4 使用方法

#### 1.4.1 添加资源到布隆过滤器

在资源创建或更新时，调用BloomFilterService将资源ID添加到布隆过滤器：

```php
// 示例：在创建博客后更新布隆过滤器
$bloomFilterService = ApplicationContext::getContainer()->get(BloomFilterService::class);
$bloomFilterService->addResource('blog', $blogId);
```

或者使用静态方法：

```php
// 使用静态方法更新布隆过滤器
ResourceCreatedListener::updateBloomFilter('blog', $blogId);
```

#### 1.4.2 配置过滤规则

在`BloomFilterMiddleware::shouldFilter()`方法中配置需要过滤的路径规则：

```php
protected function shouldFilter(string $path): bool
{
    $patterns = [
        '/blog/[0-9]+',
        '/article/[0-9]+',
        '/work/[0-9]+',
        '/mind-map/[0-9]+',
    ];
    
    // 检查路径是否匹配任一模式
    foreach ($patterns as $pattern) {
        if (preg_match('#^' . str_replace('/', '\\/', $pattern) . '$#', $path)) {
            return true;
        }
    }
    
    return false;
}
```

### 1.5 性能优化

- **布隆过滤器参数调整**：根据实际数据量调整位数组大小（$size）和哈希函数数量（$hashCount）
- **缓存预热**：系统启动时，可以从数据库加载现有资源ID到布隆过滤器
- **定期重建**：避免布隆过滤器随时间推移误判率升高，可以定期重建过滤器

## 2. 速率限制

### 2.1 概述

速率限制用于控制API接口的访问频率，防止恶意请求和资源滥用。本项目基于Hyperf内置的`hyperf/rate-limit`组件实现。

### 2.2 配置说明

速率限制配置位于：

```php
// 位于：config/autoload/rate_limit.php
```

主要配置项：

- **default**：默认限流配置（100次/秒）
- **login**：登录接口限流（5次/分钟）
- **register**：注册接口限流（3次/分钟）
- **oauth**：OAuth相关接口限流（10次/分钟）
- **comment**：评论相关接口限流（10次/分钟）
- **email_verify**：邮箱验证码接口限流（2次/分钟）
- **admin**：管理员接口限流（30次/分钟）

### 2.3 使用方法

在路由配置中通过中间件指定限流规则：

```php
// 登录接口使用login限流规则
Router::post('/api/auth/login', 'App\Controller\AuthController@login', ['middleware' => ['rate_limit:login']]);

// 评论接口使用comment限流规则
Router::post('/api/comments', 'App\Controller\CommentController@store', ['middleware' => ['jwt', 'rate_limit:comment']]);
```

### 2.4 限流策略

- **基于IP限流**：默认情况下，限流基于客户端IP地址
- **基于用户限流**：对于已认证用户，可以在中间件中修改限流键，基于用户ID进行限流
- **渐进式限流**：对于可疑的频繁请求，可以实现渐进式限流策略，逐步降低允许的请求频率

### 2.5 最佳实践

1. **不同接口采用不同限流策略**：
   - 登录、注册等敏感接口：严格限流
   - 公开查询接口：宽松限流
   - 管理员接口：中等限流

2. **合理设置限流阈值**：
   - 避免设置过低导致正常用户无法使用
   - 避免设置过高导致限流无效

3. **限流响应处理**：
   - 返回适当的HTTP状态码（429 Too Many Requests）
   - 在响应头中包含限流信息（如X-RateLimit-Limit、X-RateLimit-Remaining）

## 3. 错误处理中间件

### 3.1 概述

错误处理中间件（ErrorHandlerMiddleware）用于统一捕获和处理API请求过程中的所有异常，返回标准化的错误响应格式，提高系统的健壮性和用户体验。

### 3.2 核心功能

- **统一异常捕获**：捕获所有请求处理过程中的异常
- **环境感知的错误信息**：开发环境返回详细错误信息，生产环境返回通用错误信息
- **结构化错误响应**：统一的JSON错误响应格式
- **智能日志记录**：根据异常类型使用不同的日志级别
- **健壮性增强**：包含状态码验证、JSON编码失败处理等机制

### 3.3 关键组件

#### 3.3.1 ErrorHandlerMiddleware

```php
// 位于：app/Middleware/ErrorHandlerMiddleware.php
```

主要方法：
- `process()` - 处理请求并捕获异常
- `handleException()` - 处理异常并生成响应
- `getErrorInfo()` - 根据异常类型获取错误信息
- `logException()` - 记录异常日志
- `isDevelopmentEnvironment()` - 判断当前环境
- `validateStatusCode()` - 验证HTTP状态码有效性
- `createEmergencyResponse()` - 创建紧急响应

#### 3.3.2 异常类型判断方法

- `isNotFoundHttpException()` - 判断404异常
- `isUnauthorizedHttpException()` - 判断401异常
- `isForbiddenHttpException()` - 判断403异常
- `isBadRequestHttpException()` - 判断400异常
- `isValidationException()` - 判断验证异常

### 3.4 常量类依赖

- **StatusCode** - 定义所有HTTP状态码和业务错误码
- **ResponseMessage** - 定义所有错误响应消息

### 3.5 优化特性

1. **使用常量替代硬编码**：统一管理错误码和消息
2. **异常类型精确判断**：使用instanceof和类名匹配
3. **统一环境判断逻辑**：支持dev/development/local/test环境
4. **增强代码健壮性**：添加JSON编码失败处理和状态码验证
5. **优化日志记录**：根据异常类型设置不同日志级别，包含请求上下文信息

### 3.6 使用示例

错误处理中间件自动捕获所有异常，无需手动调用。返回的错误响应格式：

```json
// 开发环境
{
  "code": 500,
  "message": "服务器内部错误 | Exception: 错误信息 in /path/to/file.php on line 100",
  "data": null,
  "timestamp": 1620000000
}

// 生产环境
{
  "code": 500,
  "message": "服务器内部错误",
  "data": null
}
```

## 4. 部署建议

1. **Redis配置**：
   - 确保Redis服务稳定运行
   - 考虑为布隆过滤器和速率限制设置单独的Redis数据库

2. **监控告警**：
   - 监控布隆过滤器的误判率
   - 监控速率限制触发情况，及时发现异常访问
   - 监控错误日志，及时发现系统问题

3. **性能测试**：
   - 定期进行压力测试，验证中间件性能
   - 根据测试结果调整布隆过滤器参数和速率限制阈值

4. **环境配置**：
   - 确保正确设置APP_ENV环境变量
   - 开发环境设置为dev/development/local/test
   - 生产环境设置为production