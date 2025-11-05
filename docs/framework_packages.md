# 框架组件包说明文档

## 1. 后端组件包

### 1.1 核心框架组件

| 组件名称 | 版本 | 功能描述 |
| :------- | :--- | :------- |
| hyperf/framework | ~3.0.0 | Hyperf 核心框架，提供协程支持和核心功能 |
| hyperf/http-server | ~3.0.0 | HTTP 服务器组件，处理 HTTP 请求和响应 |
| hyperf/database | ~3.0.0 | 数据库抽象层，提供 ORM 功能 |
| hyperf/db-connection | ~3.0.0 | 数据库连接管理组件 |
| hyperf/command | ~3.0.0 | 命令行工具组件 |
| hyperf/config | ~3.0.0 | 配置管理组件 |
| hyperf/engine | ^2.10 | Swoole 协程引擎 |

### 1.2 缓存和数据存储组件

| 组件名称 | 版本 | 功能描述 |
| :------- | :--- | :------- |
| hyperf/cache | ~3.0.0 | 缓存管理组件 |
| hyperf/redis | ~3.0.0 | Redis 客户端，用于缓存和数据存储 |
| hyperf/memory | ~3.0.0 | 内存管理组件 |

### 1.3 认证和安全组件

| 组件名称 | 版本 | 功能描述 |
| :------- | :--- | :------- |
| 96qbhy/hyperf-auth | * | Hyperf 认证组件，提供 JWT 认证功能 |
| yurunsoft/yurun-oauth-login | * | OAuth 第三方登录组件 |

### 1.4 日志和监控组件

| 组件名称 | 版本 | 功能描述 |
| :------- | :--- | :------- |
| hyperf/logger | ~3.0.0 | 日志管理组件 |
| hyperf/tracer | ~3.0.0 | 分布式追踪组件 |

### 1.5 开发和测试组件

| 组件名称 | 版本 | 功能描述 |
| :------- | :--- | :------- |
| hyperf/devtool | ~3.0.0 | 开发工具组件 |
| hyperf/testing | ~3.0.0 | 测试组件 |
| friendsofphp/php-cs-fixer | ^3.0 | 代码格式化工具 |
| phpstan/phpstan | ^1.0 | 静态代码分析工具 |
| swoole/ide-helper | ^5.0 | Swoole IDE 助手 |

## 2. 前端组件包

### 2.1 核心框架组件

| 组件名称 | 版本 | 功能描述 |
| :------- | :--- | :------- |
| react | 19.2.0 | React 核心库 |
| react-dom | 19.2.0 | React DOM 渲染库 |
| next | 16.0.1 | Next.js 框架，提供 SSR、路由等功能 |

### 2.2 样式和 UI 组件

| 组件名称 | 版本 | 功能描述 |
| :------- | :--- | :------- |
| tailwindcss | ^4 | 实用优先的 CSS 框架 |
| @tailwindcss/postcss | ^4 | Tailwind CSS 的 PostCSS 插件 |

### 2.3 开发和构建工具

| 组件名称 | 版本 | 功能描述 |
| :------- | :--- | :------- |
| typescript | ^5 | TypeScript 支持 |
| @types/node | ^20 | Node.js 类型定义 |
| @types/react | ^19 | React 类型定义 |
| @types/react-dom | ^19 | React DOM 类型定义 |
| eslint | ^9 | 代码检查工具 |
| eslint-config-next | 16.0.1 | Next.js 的 ESLint 配置 |

## 3. 组件依赖关系

### 3.1 后端组件关系
- hyperf/framework 是所有组件的核心，其他组件都依赖于它
- hyperf/http-server 依赖于 hyperf/framework 提供 HTTP 服务能力
- hyperf/database 和 hyperf/db-connection 共同提供数据库操作能力
- 96qbhy/hyperf-auth 用于用户认证，被评论等需要权限的模块使用

### 3.2 前端组件关系
- next 框架封装了 react 和 react-dom，提供完整的应用框架
- tailwindcss 提供样式系统，与 Next.js 良好集成
- TypeScript 及相关类型定义提供类型安全支持