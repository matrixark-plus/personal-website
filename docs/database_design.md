# 数据库设计文档

## 1. 概述

本文档详细描述个人网站项目的数据库设计，包括表结构、字段说明、索引设计和表间关系。数据库采用MySQL 8.0，使用协程连接池进行连接管理。

## 2. 核心数据表

### 2.1 用户表 (users)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 用户ID |
| `username` | `VARCHAR` | 50 | `UNIQUE NOT NULL` | 用户名 |
| `email` | `VARCHAR` | 100 | `UNIQUE NOT NULL` | 邮箱 |
| `password_hash` | `VARCHAR` | 255 | `NOT NULL` | 哈希后的密码 |
| `role` | `ENUM` | - | `NOT NULL DEFAULT 'user'` | 用户角色(user/admin/guest) |
| `status` | `TINYINT` | 1 | `NOT NULL DEFAULT 1` | 用户状态(0禁用/1启用) |
| `nickname` | `VARCHAR` | 50 | `NULL` | 用户昵称 |
| `avatar` | `VARCHAR` | 255 | `NULL` | 头像URL |
| `bio` | `TEXT` | - | `NULL` | 个人简介 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |
| `last_login_at` | `DATETIME` | - | `NULL` | 最后登录时间 |

**索引设计**：
- 唯一索引：`username`、`email`
- 普通索引：`role`、`status`、`created_at`

### 2.2 博客表 (blogs)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 博客ID |
| `title` | `VARCHAR` | 255 | `NOT NULL` | 博客标题 |
| `content` | `LONGTEXT` | - | `NOT NULL` | 博客内容(Markdown格式) |
| `summary` | `VARCHAR` | 500 | `NULL` | 博客摘要 |
| `cover_image` | `VARCHAR` | 255 | `NULL` | 封面图片URL |
| `category_id` | `INT` | 11 | `NOT NULL` | 分类ID，外键关联blog_categories表 |
| `author_id` | `INT` | 11 | `NOT NULL` | 作者ID，外键关联users表 |
| `status` | `ENUM` | - | `NOT NULL DEFAULT 'draft'` | 状态(draft/published) |
| `view_count` | `INT` | 11 | `NOT NULL DEFAULT 0` | 浏览次数 |
| `comment_count` | `INT` | 11 | `NOT NULL DEFAULT 0` | 评论次数 |
| `published_at` | `DATETIME` | - | `NULL` | 发布时间 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 普通索引：`category_id`、`author_id`、`status`、`created_at`、`updated_at`、`published_at`
- 全文索引：`title`、`content`（用于搜索）

### 2.3 作品表 (works)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 作品ID |
| `title` | `VARCHAR` | 255 | `NOT NULL` | 作品标题 |
| `description` | `TEXT` | - | `NOT NULL` | 作品描述 |
| `images` | `JSON` | - | `NOT NULL` | 作品图片URL数组 |
| `project_link` | `VARCHAR` | 255 | `NULL` | 项目链接 |
| `github_link` | `VARCHAR` | 255 | `NULL` | GitHub链接 |
| `category` | `VARCHAR` | 50 | `NOT NULL` | 作品分类 |
| `author_id` | `INT` | 11 | `NOT NULL` | 作者ID，外键关联users表 |
| `is_public` | `TINYINT` | 1 | `NOT NULL DEFAULT 1` | 是否公开 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 普通索引：`category`、`author_id`、`is_public`、`created_at`

### 2.4 笔记表 (notes)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 笔记ID |
| `title` | `VARCHAR` | 255 | `NOT NULL` | 笔记标题 |
| `content` | `LONGTEXT` | - | `NOT NULL` | 笔记内容(Markdown格式) |
| `status` | `ENUM` | - | `NOT NULL DEFAULT 'draft'` | 状态(draft/published) |
| `is_public` | `TINYINT` | 1 | `NOT NULL DEFAULT 1` | 是否公开 |
| `creator_id` | `INT` | 11 | `NOT NULL` | 创建者ID，外键关联users表 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 普通索引：`status`、`is_public`、`creator_id`、`created_at`、`updated_at`
- 全文索引：`title`、`content`（用于搜索）

### 2.5 笔记版本历史表 (note_versions)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 版本ID |
| `note_id` | `INT` | 11 | `NOT NULL` | 笔记ID，外键关联notes表 |
| `content_snapshot` | `LONGTEXT` | - | `NOT NULL` | 内容快照 |
| `title_snapshot` | `VARCHAR` | 255 | `NOT NULL` | 标题快照 |
| `version_number` | `INT` | 11 | `NOT NULL` | 版本号 |
| `created_by` | `INT` | 11 | `NOT NULL` | 创建人ID，外键关联users表 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |

**索引设计**：
- 普通索引：`note_id`、`created_by`、`version_number`

### 2.6 评论表 (comments)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 评论ID |
| `user_id` | `INT` | 11 | `NOT NULL` | 用户ID，外键关联users表 |
| `post_id` | `INT` | 11 | `NOT NULL` | 关联ID（博客或作品ID） |
| `post_type` | `ENUM` | - | `NOT NULL` | 关联类型(blog/work) |
| `parent_id` | `INT` | 11 | `NULL` | 父评论ID，用于回复功能 |
| `content` | `TEXT` | - | `NOT NULL` | 评论内容 |
| `status` | `TINYINT` | 1 | `NOT NULL DEFAULT 0` | 审核状态(0待审核/1已审核/2已拒绝) |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 普通索引：`user_id`、`post_id`、`post_type`、`parent_id`、`status`、`created_at`

### 2.7 订阅表 (subscriptions)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 订阅ID |
| `email` | `VARCHAR` | 100 | `UNIQUE NOT NULL` | 订阅邮箱 |
| `token` | `VARCHAR` | 255 | `NOT NULL` | 验证令牌 |
| `status` | `TINYINT` | 1 | `NOT NULL DEFAULT 0` | 订阅状态(0未验证/1已验证/2已取消) |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 唯一索引：`email`
- 普通索引：`token`、`status`

### 2.8 用户分析表 (user_analytics)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT` | 20 | `PRIMARY KEY AUTO_INCREMENT` | 记录ID |
| `user_id` | `INT` | 11 | `NULL` | 用户ID（可为空，访客） |
| `session_id` | `VARCHAR` | 255 | `NOT NULL` | 会话ID |
| `event_type` | `VARCHAR` | 50 | `NOT NULL` | 事件类型(page_view/click/scroll等) |
| `event_data` | `JSON` | - | `NULL` | 事件详细数据 |
| `url` | `VARCHAR` | 500 | `NOT NULL` | 访问URL |
| `referrer` | `VARCHAR` | 500 | `NULL` | 来源URL |
| `user_agent` | `VARCHAR` | 255 | `NOT NULL` | 用户代理 |
| `ip_address` | `VARCHAR` | 45 | `NOT NULL` | IP地址 |
| `browser` | `VARCHAR` | 50 | `NULL` | 浏览器 |
| `device` | `VARCHAR` | 50 | `NULL` | 设备类型 |
| `os` | `VARCHAR` | 50 | `NULL` | 操作系统 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |

**索引设计**：
- 普通索引：`user_id`、`session_id`、`event_type`、`url`、`created_at`

### 2.9 联系表单表 (contact_forms)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 表单提交ID |
| `name` | `VARCHAR` | 100 | `NOT NULL` | 提交人姓名 |
| `email` | `VARCHAR` | 100 | `NOT NULL` | 提交人邮箱 |
| `subject` | `VARCHAR` | 255 | `NOT NULL` | 主题 |
| `message` | `TEXT` | - | `NOT NULL` | 内容 |
| `status` | `TINYINT` | 1 | `NOT NULL DEFAULT 0` | 处理状态(0未处理/1已处理) |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 提交时间 |
| `processed_at` | `DATETIME` | - | `NULL` | 处理时间 |
| `processed_by` | `INT` | 11 | `NULL` | 处理人ID，外键关联users表 |

**索引设计**：
- 普通索引：`email`、`status`、`created_at`

### 2.10 系统配置表 (system_configs)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 配置ID |
| `config_key` | `VARCHAR` | 100 | `UNIQUE NOT NULL` | 配置项名称 |
| `config_value` | `TEXT` | - | `NOT NULL` | 配置值 |
| `description` | `VARCHAR` | 255 | `NULL` | 配置项描述 |
| `type` | `VARCHAR` | 50 | `NOT NULL DEFAULT 'string'` | 配置类型(string/json/number/boolean) |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 唯一索引：`config_key`
- 普通索引：`type`

## 3. 脑图相关表

### 3.1 脑图根节点表 (mindmap_roots)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 根节点ID |
| `title` | `VARCHAR` | 255 | `NOT NULL` | 根节点标题 |
| `description` | `TEXT` | - | `NULL` | 描述 |
| `screenshot_path` | `VARCHAR` | 255 | `NULL` | 脑图截图路径 |
| `creator_id` | `INT` | 11 | `NOT NULL` | 创建者ID，外键关联users表 |
| `is_public` | `TINYINT` | 1 | `NOT NULL DEFAULT 1` | 是否公开 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 普通索引：`creator_id`、`is_public`、`created_at`

### 3.2 脑图节点表 (mindmap_nodes)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 节点ID |
| `root_id` | `INT` | 11 | `NOT NULL` | 根节点ID，外键关联mindmap_roots表 |
| `parent_id` | `INT` | 11 | `NULL` | 父节点ID，自关联 |
| `title` | `VARCHAR` | 255 | `NOT NULL` | 节点标题 |
| `node_type` | `ENUM` | - | `NOT NULL` | 节点类型(node/note_link) |
| `note_id` | `INT` | 11 | `NULL` | 关联笔记ID，外键关联notes表（仅node_type=note_link时有效） |
| `position_x` | `FLOAT` | - | `NULL` | 节点X坐标 |
| `position_y` | `FLOAT` | - | `NULL` | 节点Y坐标 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 普通索引：`root_id`、`parent_id`、`note_id`、`node_type`

### 3.3 节点链接表 (node_links)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 链接ID |
| `source_node_id` | `INT` | 11 | `NOT NULL` | 源节点ID，外键关联mindmap_nodes表 |
| `target_node_id` | `INT` | 11 | `NOT NULL` | 目标节点ID，外键关联mindmap_nodes表 |
| `link_type` | `VARCHAR` | 50 | `NOT NULL DEFAULT 'bidirectional'` | 链接类型(bidirectional/unidirectional) |
| `label` | `VARCHAR` | 100 | `NULL` | 链接标签 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |

**索引设计**：
- 普通索引：`source_node_id`、`target_node_id`、`link_type`
- 复合索引：`(source_node_id, target_node_id)`、`(target_node_id, source_node_id)`

## 4. 分类标签表

### 4.1 博客分类表 (blog_categories)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 分类ID |
| `name` | `VARCHAR` | 50 | `UNIQUE NOT NULL` | 分类名称 |
| `slug` | `VARCHAR` | 50 | `UNIQUE NOT NULL` | 分类别名(URL友好) |
| `parent_id` | `INT` | 11 | `NULL` | 父分类ID，自关联，支持多级分类 |
| `description` | `VARCHAR` | 255 | `NULL` | 分类描述 |
| `sort_order` | `INT` | 11 | `NOT NULL DEFAULT 0` | 排序顺序 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 唯一索引：`name`、`slug`
- 普通索引：`parent_id`、`sort_order`

### 4.2 博客标签表 (blog_tags)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 标签ID |
| `name` | `VARCHAR` | 50 | `UNIQUE NOT NULL` | 标签名称 |
| `slug` | `VARCHAR` | 50 | `UNIQUE NOT NULL` | 标签别名(URL友好) |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |

**索引设计**：
- 唯一索引：`name`、`slug`

### 4.3 博客标签关联表 (blog_tag_relations)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 关联ID |
| `blog_id` | `INT` | 11 | `NOT NULL` | 博客ID，外键关联blogs表 |
| `tag_id` | `INT` | 11 | `NOT NULL` | 标签ID，外键关联blog_tags表 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |

**索引设计**：
- 唯一索引：`(blog_id, tag_id)`（避免重复关联）
- 普通索引：`blog_id`、`tag_id`

## 5. 表间关系

### 5.1 主要关系图

```
用户表 (users)
  |-- 1:N -- 博客表 (blogs)
  |-- 1:N -- 作品表 (works)
  |-- 1:N -- 笔记表 (notes)
  |-- 1:N -- 评论表 (comments)
  |-- 1:N -- 笔记版本历史表 (note_versions)
  |-- 1:N -- 脑图根节点表 (mindmap_roots)
  |-- 1:N -- 联系表单表 (contact_forms) [处理人]

博客表 (blogs)
  |-- N:1 -- 博客分类表 (blog_categories)
  |-- N:N -- 博客标签表 (blog_tags) [通过blog_tag_relations关联]
  |-- 1:N -- 评论表 (comments)

笔记表 (notes)
  |-- 1:N -- 笔记版本历史表 (note_versions)
  |-- N:1 -- 脑图节点表 (mindmap_nodes) [当节点类型为note_link时]

脑图根节点表 (mindmap_roots)
  |-- 1:N -- 脑图节点表 (mindmap_nodes)

脑图节点表 (mindmap_nodes)
  |-- N:M -- 节点链接表 (node_links)
```

### 5.2 外键约束

所有外键约束遵循以下原则：
- 删除操作：`ON DELETE CASCADE` 或 `ON DELETE SET NULL`，根据业务需求选择
- 更新操作：`ON UPDATE CASCADE`

## 6. 数据库优化策略

### 6.1 索引优化

- 为频繁查询的字段创建索引
- 避免在高基数列创建过多索引
- 对常用查询组合创建复合索引
- 定期分析和优化索引使用情况

### 6.2 查询优化

- 使用连接池管理数据库连接
- 避免SELECT * 查询
- 使用LIMIT限制结果集大小
- 优化JOIN操作，确保关联字段有索引
- 对复杂查询使用EXPLAIN分析执行计划

### 6.3 存储优化

- 使用合适的数据类型，避免浪费空间
- 对大文本使用TEXT类型，不使用VARCHAR
- 对结构化数据考虑使用JSON类型
- 定期清理无效数据，维护表空间

### 6.4 备份与恢复

- 定期全量备份
- 设置增量备份策略
- 建立备份验证机制
- 制定灾难恢复计划

## 7. 安全性考虑

- 使用参数化查询防止SQL注入
- 敏感数据加密存储
- 限制数据库用户权限
- 定期审计数据库访问日志
- 实施数据库防火墙策略