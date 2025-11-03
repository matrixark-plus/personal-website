# API服务模块设计文档

## 1. 模块概述

### 1.1 功能描述
API服务模块为前端和后台管理系统提供统一的数据接口，基于Hyperf框架实现高性能的RESTful API服务。

### 1.2 模块位置
- 项目目录：backend/
- API路由前缀：/api
- 控制器目录：backend/app/Controller/Api/

## 2. 技术架构

### 2.1 核心技术
- 后端框架：Hyperf（基于Swoole的协程框架）
- 数据库：MySQL 8.0
- 缓存：Redis 7
- API规范：RESTful API

### 2.2 架构设计
- 分层架构：控制器层、服务层、数据访问层
- 协程支持：全链路协程化处理
- 中间件机制：统一处理认证、限流、日志等
- 依赖注入：服务容器管理对象依赖

#### Hyperf标准符合性
- 严格遵循Hyperf的分层架构设计原则
- 使用Hyperf的依赖注入容器管理服务
- 遵循Hyperf的协程编程模型
- 集成Hyperf的标准中间件体系

## 3. API设计规范

### 3.1 请求规范
- 统一使用GET/POST两种HTTP方法
- GET用于数据查询操作
- POST用于数据创建、更新和删除操作
- URL路径明确表达资源操作意图

### 3.2 响应规范
```json
{
  "success": true,
  "code": 200,
  "message": "操作成功",
  "data": {},
  "timestamp": 1234567890,
  "request_id": "uuid"
}
```

### 3.3 错误处理
- 统一异常处理机制
- 详细的错误信息返回
- HTTP状态码与业务码映射
- 开发环境与生产环境差异化错误信息

#### Hyperf标准符合性
- 使用Hyperf的异常处理器机制
- 遵循Hyperf的响应格式规范
- 集成Hyperf的日志组件进行错误记录

## 4. 核心功能模块

### 4.1 认证模块

#### 功能描述
处理用户身份验证和权限控制。

#### 核心接口
- POST /api/auth/login - 用户登录
- POST /api/auth/register - 用户注册
- GET /api/auth/profile - 获取用户信息
- POST /api/auth/logout - 用户登出

#### 安全机制
- JWT令牌认证
- 密码加密存储
- 登录频率限制
- 会话管理

#### Hyperf标准符合性
- 使用Hyperf官方推荐的JWT组件
- 集成Hyperf的密码加密工具
- 遵循Hyperf的安全最佳实践

### 4.2 用户模块

#### 功能描述
管理用户基本信息和权限。

#### 核心接口
- GET /api/users - 获取用户列表
- GET /api/users/{id} - 获取用户详情
- POST /api/users - 创建用户
- PUT /api/users/{id} - 更新用户信息
- DELETE /api/users/{id} - 删除用户

#### 数据结构
- 用户基本信息
- 角色权限关联
- 用户状态管理

### 4.3 博客模块

#### 功能描述
提供博客文章的增删改查功能。

#### 核心接口
- GET /api/blog/posts - 获取博客列表
- GET /api/blog/posts/{id} - 获取博客详情
- POST /api/blog/posts - 创建博客
- PUT /api/blog/posts/{id} - 更新博客
- DELETE /api/blog/posts/{id} - 删除博客

#### 功能特性
- 分类和标签管理
- Markdown内容支持
- 搜索和筛选功能

### 4.4 作品模块

#### 功能描述
管理系统中的作品信息。

#### 核心接口
- GET /api/portfolio/items - 获取作品列表
- GET /api/portfolio/items/{id} - 获取作品详情
- POST /api/portfolio/items - 创建作品
- PUT /api/portfolio/items/{id} - 更新作品
- DELETE /api/portfolio/items/{id} - 删除作品

#### 功能特性
- 分类管理
- 图片上传处理
- 外链支持

### 4.5 笔记模块

#### 功能描述
提供脑图笔记的管理功能。

#### 核心接口
- GET /api/notes/mindmap/roots - 获取脑图根节点
- GET /api/notes/mindmap/roots/{id}/data - 获取脑图数据
- POST /api/notes/mindmap/roots - 创建根节点
- POST /api/notes/mindmap/nodes - 创建节点
- PUT /api/notes/mindmap/nodes/{id} - 更新节点

#### 功能特性
- 脑图数据结构
- 双向链接支持
- Markdown内容编辑

### 4.6 评论模块

#### 功能描述
处理用户评论的管理。

#### 核心接口
- GET /api/comments - 获取评论列表
- POST /api/comments - 创建评论
- PUT /api/comments/{id}/review - 审核评论
- DELETE /api/comments/{id} - 删除评论

#### 功能特性
- 评论审核机制
- 层级评论支持
- 状态管理

## 5. 性能优化

### 5.1 协程优化
- MySQL和Redis协程连接池
- 最大连接数：20-50
- 连接超时：10秒
- 等待超时：3秒

### 5.2 缓存策略
- Redis缓存热点数据
- JWT令牌缓存
- 查询结果缓存
- 缓存预热机制

### 5.3 数据库优化
- 合理的索引设计
- 查询语句优化
- 连接池配置
- 分表分库策略

## 6. 安全机制

### 6.1 认证安全
- JWT令牌安全传输
- HTTPS强制使用
- 密码强度要求
- 登录失败限制

### 6.2 数据安全
- SQL注入防护
- XSS攻击防护
- CSRF防护
- 敏感数据加密

### 6.3 访问安全
- IP频率限制
- 请求参数验证
- 数据权限控制
- 操作日志记录

## 7. 中间件设计

### 7.1 认证中间件
- JWT令牌验证
- 用户权限检查
- 会话状态维护

### 7.2 限流中间件
- 基于Redis的滑动窗口限流
- IP地址频率限制
- 用户级别限流

### 7.3 日志中间件
- 请求日志记录
- 响应日志记录
- 异常日志记录
- 性能指标收集

## 8. 部署架构

### 8.1 线上部署
- 阿里云ECS单机部署
- systemd服务管理
- Nginx反向代理
- SSL证书配置

### 8.2 开发部署
- Docker Compose容器化部署
- 热重载支持
- 环境变量配置
- 日志文件管理