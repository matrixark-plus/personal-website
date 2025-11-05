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
- 唯一索引：`username`
- 复合索引：`(email, password_hash)`（覆盖登录验证查询）
- 普通索引：`created_at`
- 说明：移除了低选择性的单独`role`和`status`索引，将`email`改为复合索引以支持覆盖查询

### 2.2 博客表 (blogs)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 博客ID |
| `title` | `VARCHAR` | 255 | `NOT NULL` | 博客标题 |
| `content` | `LONGTEXT` | - | `NOT NULL` | 博客内容(Markdown格式) |
| `summary` | `VARCHAR` | 500 | `NULL` | 博客摘要 |
| `cover_image` | `VARCHAR` | 255 | `NULL` | 封面图片URL |
| `category_id` | `INT` | 11 | `NOT NULL` | 分类ID，关联blog_categories表 |
| `author_id` | `INT` | 11 | `NOT NULL` | 作者ID，关联users表 |
| `status` | `ENUM` | - | `NOT NULL DEFAULT 'draft'` | 状态(draft/published) |
| `view_count` | `INT` | 11 | `NOT NULL DEFAULT 0` | 浏览次数 |
| `comment_count` | `INT` | 11 | `NOT NULL DEFAULT 0` | 评论次数 |
| `published_at` | `DATETIME` | - | `NULL` | 发布时间 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 复合索引：`(status, created_at)`（支持按状态筛选并按创建时间排序）
- 普通索引：`category_id`、`author_id`
- 全文索引：`title`、`content`（用于搜索）
- 说明：移除了单独的时间字段索引，合并为复合索引提高查询效率

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
| `author_id` | `INT` | 11 | `NOT NULL` | 作者ID，关联users表 |
| `is_public` | `TINYINT` | 1 | `NOT NULL DEFAULT 1` | 是否公开 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 复合索引：`(is_public, created_at)`（支持公开状态排序查询）
- 普通索引：`author_id`

### 2.4 笔记表 (notes)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 笔记ID |
| `title` | `VARCHAR` | 255 | `NOT NULL` | 笔记标题 |
| `content` | `LONGTEXT` | - | `NOT NULL` | 笔记内容(Markdown格式) |
| `status` | `ENUM` | - | `NOT NULL DEFAULT 'draft'` | 状态(draft/published) |
| `is_public` | `TINYINT` | 1 | `NOT NULL DEFAULT 1` | 是否公开 |
| `creator_id` | `INT` | 11 | `NOT NULL` | 创建者ID，关联users表 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 复合索引：`(status, is_public, created_at)`（支持多条件筛选和排序）
- 普通索引：`creator_id`
- 全文索引：`title`、`content`（用于搜索）

### 2.5 笔记版本历史表 (note_versions)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 版本ID |
| `note_id` | `INT` | 11 | `NOT NULL` | 笔记ID，关联notes表 |
| `content_snapshot` | `LONGTEXT` | - | `NOT NULL` | 内容快照 |
| `title_snapshot` | `VARCHAR` | 255 | `NOT NULL` | 标题快照 |
| `version_number` | `INT` | 11 | `NOT NULL` | 版本号 |
| `created_by` | `INT` | 11 | `NOT NULL` | 创建人ID，关联users表 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |

**索引设计**：
- 复合索引：`(note_id, version_number)`（支持笔记版本查询和排序）
- 普通索引：`created_by`

### 2.6 评论表 (comments)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 评论ID |
| `user_id` | `INT` | 11 | `NOT NULL` | 用户ID，关联users表 |
| `post_id` | `INT` | 11 | `NOT NULL` | 关联ID（博客或作品ID） |
| `post_type` | `ENUM` | - | `NOT NULL` | 关联类型(blog/work) |
| `parent_id` | `INT` | 11 | `NULL` | 父评论ID，用于回复功能 |
| `content` | `TEXT` | - | `NOT NULL` | 评论内容（限制1000个中文字符） |
| `status` | `TINYINT` | 1 | `NOT NULL DEFAULT 0` | 审核状态(0待审核/1已审核通过/2已拒绝) |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 复合索引：`(post_id, post_type)`（支持按关联ID和类型查询评论）
- 复合索引：`(status, created_at)`（支持审核状态和时间排序）
- 普通索引：`user_id`、`parent_id`

**审核流程说明**：
1. 当用户提交新评论时，评论状态默认为0（待审核）
2. 系统通知管理员有新评论需要审核
3. 管理员可以通过后台审核评论，将状态改为1（已通过）或2（已拒绝）
4. 只有状态为1的评论才会在前端展示给普通用户
5. 当评论通过审核后，系统会自动更新关联内容（博客/作品）的评论计数
6. 支持批量审核功能，提高管理效率
7. 管理员可以查看所有状态的评论，包括待审核和已拒绝的评论

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
- 复合索引：`(token, status)`（支持验证令牌和状态查询）

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
- 复合索引：`(session_id, created_at)`（支持会话分析查询）
- 复合索引：`(event_type, created_at)`（支持事件类型统计）
- 普通索引：`user_id`
- 说明：移除了选择性可能不高的`url`索引，优化为更常用的查询模式

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
| `processed_by` | `INT` | 11 | `NULL` | 处理人ID，关联users表 |

**索引设计**：
- 复合索引：`(status, created_at)`（支持状态筛选和时间排序）
- 普通索引：`email`

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
- 复合索引：`(config_key, config_value)`（覆盖配置查询，避免回表）
- 说明：将唯一索引改为复合索引以支持覆盖查询，移除了低选择性的`type`索引

## 3. 脑图相关表

### 3.1 脑图根节点表 (mindmap_roots)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 根节点ID |
| `title` | `VARCHAR` | 255 | `NOT NULL` | 根节点标题 |
| `description` | `TEXT` | - | `NULL` | 描述 |
| `screenshot_path` | `VARCHAR` | 255 | `NULL` | 脑图截图路径 |
| `creator_id` | `INT` | 11 | `NOT NULL` | 创建者ID，关联users表 |
| `is_public` | `TINYINT` | 1 | `NOT NULL DEFAULT 1` | 是否公开 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 复合索引：`(is_public, created_at)`（支持公开状态排序查询）
- 复合索引：`(creator_id, created_at)`（支持用户的脑图列表）
- 普通索引：`title`（支持标题搜索）

### 3.2 脑图节点表 (mindmap_nodes)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 节点ID |
| `root_id` | `INT` | 11 | `NOT NULL` | 根节点ID，关联mindmap_roots表 |
| `parent_id` | `INT` | 11 | `NULL` | 父节点ID，自关联 |
| `title` | `VARCHAR` | 255 | `NOT NULL` | 节点标题 |
| `node_type` | `ENUM` | - | `NOT NULL` | 节点类型(node/note_link) |
| `note_id` | `INT` | 11 | `NULL` | 关联笔记ID（仅node_type=note_link时有效） |
| `position_x` | `FLOAT` | - | `NULL` | 节点X坐标 |
| `position_y` | `FLOAT` | - | `NULL` | 节点Y坐标 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |
| `updated_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | 更新时间 |

**索引设计**：
- 复合索引：`(root_id, parent_id)`（支持获取脑图的节点树结构）
- 复合索引：`(node_type, note_id)`（支持按类型和关联笔记查询）

### 3.3 节点链接表 (node_links)

| 字段名 | 数据类型 | 长度 | 约束 | 描述 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `INT` | 11 | `PRIMARY KEY AUTO_INCREMENT` | 链接ID |
| `source_node_id` | `INT` | 11 | `NOT NULL` | 源节点ID，关联mindmap_nodes表 |
| `target_node_id` | `INT` | 11 | `NOT NULL` | 目标节点ID，关联mindmap_nodes表 |
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
- 复合索引：`(parent_id, sort_order)`（支持多级分类排序查询）

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
| `blog_id` | `INT` | 11 | `NOT NULL` | 博客ID，关联blogs表 |
| `tag_id` | `INT` | 11 | `NOT NULL` | 标签ID，关联blog_tags表 |
| `created_at` | `DATETIME` | - | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | 创建时间 |

**索引设计**：
- 唯一索引：`(blog_id, tag_id)`（避免重复关联，同时作为复合索引支持查询）
- 普通索引：`tag_id`
- 说明：保留`tag_id`索引用于反向查询，移除冗余的`blog_id`单独索引

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

### 5.2 表关系管理

- 表间关系通过应用层代码维护
- 业务逻辑中确保关联数据的完整性和一致性
- 适当的事务处理保证数据操作的原子性

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

## 8. 迁移文件管理最佳实践

### 8.1 迁移文件创建与修改原则

1. **创建完整表结构**：新表创建时应包含所有必要字段，避免后续频繁更新
2. **避免使用after()方法**：在Hyperf框架中，`after()`方法可能导致SQL语法错误，建议按自然顺序定义字段
3. **更新迁移文件**：对于未执行的迁移，应直接修改原文件；对于已执行的迁移，创建新的update迁移文件
4. **复合迁移管理**：当多个迁移文件相互依赖或冲突时，考虑合并为单个完整迁移
5. **清理旧迁移**：移除不必要或冲突的迁移文件，保持迁移历史的清晰

### 8.2 数据库重置流程

1. **删除现有表**：使用`DROP TABLE IF EXISTS`语句按依赖关系删除所有表
2. **设置外键检查**：操作前关闭外键检查`SET FOREIGN_KEY_CHECKS = 0;`，操作后开启`SET FOREIGN_KEY_CHECKS = 1;`
3. **执行初始化脚本**：直接在数据库容器中执行完整的初始化SQL脚本
4. **验证表结构**：执行完成后检查所有表是否正确创建
5. **插入基础数据**：初始化必要的默认数据（管理员账户、配置项等）