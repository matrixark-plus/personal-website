# API接口文档

## 1. 概述

本文档详细描述个人网站项目的后端API接口设计，包括接口路径、请求参数、响应格式等。API采用简化的HTTP方法设计，使用GET/POST操作类型，所有接口均遵循统一的响应格式规范。

## 2. 接口基础规范

### 2.1 基础URL

所有API接口的基础URL格式为：
```
/api/v1/{module}/{action}
```

### 2.2 HTTP方法

采用GET/POST简化操作类型：
- `GET`: 用于查询、获取数据
- `POST`: 用于创建、更新、删除数据及其他操作

### 2.3 请求格式

- **查询参数**：用于过滤、排序、分页等
- **请求体**：JSON格式，Content-Type: application/json
- **路径参数**：用于标识资源ID

### 2.4 响应格式

统一JSON格式响应：

#### 成功响应
```json
{
  "code": 200,
  "message": "success",
  "data": { ... }
}
```

#### 错误响应
```json
{
  "code": 错误码,
  "message": "错误信息",
  "data": null
}
```

### 2.5 错误码体系

| 错误码 | 描述 |
| :--- | :--- |
| 200 | 成功 |
| 400 | 请求参数错误 |
| 401 | 未授权 |
| 403 | 权限不足 |
| 404 | 资源不存在 |
| 500 | 服务器内部错误 |
| 1001 | 用户相关错误 |
| 1002 | 内容相关错误 |
| 1003 | 脑图相关错误 |
| 1004 | 系统服务相关错误 |

## 3. 用户认证接口

### 3.1 用户注册

#### 请求信息
- **URL**: `/api/v1/auth/register`
- **方法**: `POST`
- **请求体**:
```json
{
  "username": "string",
  "email": "string",
  "password": "string",
  "nickname": "string" (可选)
}
```

#### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "user_id": 1,
    "username": "string",
    "email": "string",
    "token": "string"
  }
}
```

### 3.2 用户登录

#### 请求信息
- **URL**: `/api/v1/auth/login`
- **方法**: `POST`
- **请求体**:
```json
{
  "email": "string",
  "password": "string"
}
```

#### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "user_id": 1,
    "username": "string",
    "email": "string",
    "role": "string",
    "token": "string"
  }
}
```

### 3.3 获取用户信息

#### 请求信息
- **URL**: `/api/v1/auth/userinfo`
- **方法**: `GET`
- **Headers**: `Authorization: Bearer {token}`

#### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "username": "string",
    "email": "string",
    "nickname": "string",
    "avatar": "string",
    "bio": "string",
    "role": "string",
    "created_at": "datetime"
  }
}
```

### 3.4 更新用户信息

#### 请求信息
- **URL**: `/api/v1/auth/update`
- **方法**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **请求体**:
```json
{
  "nickname": "string" (可选),
  "avatar": "string" (可选),
  "bio": "string" (可选)
}
```

#### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "username": "string",
    "nickname": "string",
    "avatar": "string",
    "bio": "string"
  }
}
```

### 3.5 第三方登录（微信）

#### 请求信息
- **URL**: `/api/v1/auth/wechat`
- **方法**: `POST`
- **请求体**:
```json
{
  "code": "string" (微信授权code)
}
```

#### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "user_id": 1,
    "username": "string",
    "token": "string",
    "is_new_user": false
  }
}
```

## 4. 内容管理接口

### 4.1 博客文章管理

#### 4.1.1 获取博客列表

##### 请求信息
- **URL**: `/api/v1/blog/list`
- **方法**: `GET`
- **查询参数**:
  - `page`: 页码，默认1
  - `limit`: 每页数量，默认10
  - `category_id`: 分类ID（可选）
  - `tag_id`: 标签ID（可选）
  - `status`: 状态（可选，published/draft）
  - `order_by`: 排序字段（可选，created_at/published_at）
  - `order`: 排序方式（可选，asc/desc）

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 100,
    "page": 1,
    "limit": 10,
    "items": [
      {
        "id": 1,
        "title": "string",
        "summary": "string",
        "cover_image": "string",
        "category_id": 1,
        "category_name": "string",
        "author_id": 1,
        "author_name": "string",
        "view_count": 100,
        "comment_count": 10,
        "status": "published",
        "published_at": "datetime",
        "created_at": "datetime",
        "tags": ["tag1", "tag2"]
      }
    ]
  }
}
```

#### 4.1.2 获取博客详情

##### 请求信息
- **URL**: `/api/v1/blog/detail`
- **方法**: `GET`
- **查询参数**:
  - `id`: 博客ID

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "title": "string",
    "content": "string",
    "cover_image": "string",
    "category_id": 1,
    "category_name": "string",
    "author_id": 1,
    "author_name": "string",
    "view_count": 100,
    "comment_count": 10,
    "status": "published",
    "published_at": "datetime",
    "created_at": "datetime",
    "updated_at": "datetime",
    "tags": [
      {"id": 1, "name": "tag1"},
      {"id": 2, "name": "tag2"}
    ]
  }
}
```

#### 4.1.3 创建博客

##### 请求信息
- **URL**: `/api/v1/blog/create`
- **方法**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **请求体**:
```json
{
  "title": "string",
  "content": "string",
  "summary": "string" (可选),
  "cover_image": "string" (可选),
  "category_id": 1,
  "tags": [1, 2, 3],
  "status": "draft" (可选，draft/published)
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "title": "string"
  }
}
```

### 4.2 作品集管理

#### 4.2.1 获取作品列表

##### 请求信息
- **URL**: `/api/v1/work/list`
- **方法**: `GET`
- **查询参数**:
  - `page`: 页码，默认1
  - `limit`: 每页数量，默认10
  - `category`: 分类（可选）
  - `order_by`: 排序字段（可选，created_at）
  - `order`: 排序方式（可选，asc/desc）

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 50,
    "page": 1,
    "limit": 10,
    "items": [
      {
        "id": 1,
        "title": "string",
        "description": "string",
        "images": ["url1", "url2"],
        "project_link": "string",
        "github_link": "string",
        "category": "string",
        "author_id": 1,
        "author_name": "string",
        "created_at": "datetime"
      }
    ]
  }
}
```

### 4.3 笔记管理

#### 4.3.1 获取笔记详情

##### 请求信息
- **URL**: `/api/v1/note/detail`
- **方法**: `GET`
- **查询参数**:
  - `id`: 笔记ID

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "title": "string",
    "content": "string",
    "status": "published",
    "is_public": 1,
    "creator_id": 1,
    "creator_name": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
  }
}
```

### 4.4 评论管理

#### 4.4.1 获取评论列表

##### 请求信息
- **URL**: `/api/v1/comment/list`
- **方法**: `GET`
- **查询参数**:
  - `page`: 页码，默认1
  - `limit`: 每页数量，默认20
  - `post_id`: 关联ID（博客或作品ID）
  - `post_type`: 关联类型（blog/work）
  - `include_pending`: 是否包含待审核评论（可选，默认false，仅管理员可设置为true）

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 100,
    "page": 1,
    "limit": 20,
    "items": [
      {
        "id": 1,
        "user_id": 1,
        "username": "string",
        "avatar": "string",
        "content": "string",
        "parent_id": null,
        "replies": [],
        "created_at": "datetime",
        "status": 1 // 1已审核通过，仅对管理员可见
      }
    ]
  }
}
```

#### 4.4.2 发表评论

##### 请求信息
- **URL**: `/api/v1/comment/create`
- **方法**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **请求体**:
```json
{
  "post_id": 1,
  "post_type": "blog",
  "parent_id": null (可选，回复评论时填写),
  "content": "string" // 限制1000个中文字符
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "评论提交成功，等待审核",
  "data": {
    "id": 1,
    "content": "string",
    "created_at": "datetime",
    "status": 0 // 0待审核
  }
}
```

#### 4.4.3 获取待审核评论列表（管理员）

##### 请求信息
- **URL**: `/api/v1/comment/pending`
- **方法**: `GET`
- **Headers**: `Authorization: Bearer {token}`
- **查询参数**:
  - `page`: 页码，默认1
  - `limit`: 每页数量，默认20
  - `post_type`: 关联类型（可选，blog/work）

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 10,
    "page": 1,
    "limit": 20,
    "items": [
      {
        "id": 1,
        "user_id": 1,
        "username": "string",
        "avatar": "string",
        "content": "string",
        "post_id": 1,
        "post_type": "blog",
        "post_title": "文章标题",
        "parent_id": null,
        "created_at": "datetime"
      }
    ]
  }
}
```

#### 4.4.4 审核评论（管理员）

##### 请求信息
- **URL**: `/api/v1/comment/approve`
- **方法**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **请求体**:
```json
{
  "comment_id": 1 // 单个评论ID
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "评论审核通过",
  "data": {
    "id": 1,
    "status": 1
  }
}
```

#### 4.4.5 批量审核评论（管理员）

##### 请求信息
- **URL**: `/api/v1/comment/batch-approve`
- **方法**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **请求体**:
```json
{
  "comment_ids": [1, 2, 3] // 评论ID数组
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "批量审核成功",
  "data": {
    "approved_count": 3
  }
}
```

#### 4.4.6 拒绝评论（管理员）

##### 请求信息
- **URL**: `/api/v1/comment/reject`
- **方法**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **请求体**:
```json
{
  "comment_id": 1, // 单个评论ID
  "reason": "string" // 拒绝原因（可选）
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "评论已拒绝",
  "data": {
    "id": 1,
    "status": 2
  }
}
```

#### 4.4.7 批量拒绝评论（管理员）

##### 请求信息
- **URL**: `/api/v1/comment/batch-reject`
- **方法**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **请求体**:
```json
{
  "comment_ids": [1, 2, 3], // 评论ID数组
  "reason": "string" // 拒绝原因（可选）
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "批量拒绝成功",
  "data": {
    "rejected_count": 3
  }
}
```

### 4.5 RSS订阅

#### 4.5.1 订阅博客

##### 请求信息
- **URL**: `/api/v1/subscribe/blog`
- **方法**: `POST`
- **请求体**:
```json
{
  "email": "string"
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "订阅成功，请查收验证邮件",
  "data": null
}
```

### 4.6 联系表单

#### 4.6.1 提交联系表单

##### 请求信息
- **URL**: `/api/v1/contact/submit`
- **方法**: `POST`
- **请求体**:
```json
{
  "name": "string",
  "email": "string",
  "subject": "string",
  "message": "string"
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "提交成功，我们会尽快与您联系",
  "data": null
}
```

### 4.7 社交媒体分享

#### 4.7.1 获取分享配置

##### 请求信息
- **URL**: `/api/v1/share/config`
- **方法**: `GET`
- **查询参数**:
  - `type`: 内容类型（blog/work）
  - `id`: 内容ID

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "shareable_platforms": ["wechat", "weibo", "twitter", "facebook"],
    "share_url": "string",
    "title": "string",
    "description": "string",
    "image": "string"
  }
}
```

## 5. 脑图管理接口

### 5.1 脑图根节点管理

#### 5.1.1 获取根节点列表

##### 请求信息
- **URL**: `/api/v1/mindmap/roots`
- **方法**: `GET`
- **查询参数**:
  - `page`: 页码，默认1
  - `limit`: 每页数量，默认10

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 20,
    "page": 1,
    "limit": 10,
    "items": [
      {
        "id": 1,
        "title": "string",
        "description": "string",
        "screenshot_path": "string",
        "creator_id": 1,
        "creator_name": "string",
        "created_at": "datetime"
      }
    ]
  }
}
```

#### 5.1.2 获取脑图数据

##### 请求信息
- **URL**: `/api/v1/mindmap/data`
- **方法**: `GET`
- **查询参数**:
  - `root_id`: 根节点ID

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "root": {
      "id": 1,
      "title": "string",
      "description": "string"
    },
    "nodes": [
      {
        "id": 1,
        "root_id": 1,
        "parent_id": null,
        "title": "string",
        "node_type": "node",
        "note_id": null,
        "position_x": 100,
        "position_y": 100
      },
      {
        "id": 2,
        "root_id": 1,
        "parent_id": 1,
        "title": "string",
        "node_type": "note_link",
        "note_id": 5,
        "position_x": 200,
        "position_y": 100
      }
    ],
    "links": [
      {
        "id": 1,
        "source_node_id": 1,
        "target_node_id": 2,
        "link_type": "bidirectional",
        "label": "string"
      }
    ]
  }
}
```

## 6. 系统服务接口

### 6.1 邮件服务

#### 6.1.1 发送邮件

##### 请求信息
- **URL**: `/api/v1/email/send`
- **方法**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **请求体**:
```json
{
  "to": "string",
  "subject": "string",
  "template": "string" (可选，邮件模板名称),
  "data": { ... } (可选，模板数据)
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "邮件发送成功",
  "data": null
}
```

#### 6.1.2 发送验证码

##### 请求信息
- **URL**: `/api/v1/email/verify-code`
- **方法**: `POST`
- **请求体**:
```json
{
  "email": "string"
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "验证码已发送，请注意查收",
  "data": null
}
```

### 6.2 仪表板数据

#### 6.2.1 获取统计数据

##### 请求信息
- **URL**: `/api/v1/dashboard/stats`
- **方法**: `GET`
- **Headers**: `Authorization: Bearer {token}`
- **查询参数**:
  - `period`: 时间段（可选，day/week/month/year）

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total_visits": 1000,
    "total_users": 100,
    "total_blogs": 50,
    "total_comments": 200,
    "total_works": 20,
    "total_notes": 30,
    "recent_visits": [100, 120, 90, 150, 130, 110, 140],
    "top_visited_pages": [
      {"url": "string", "count": 500},
      {"url": "string", "count": 400}
    ],
    "new_users_count": 10,
    "pending_comments": 5
  }
}
```

### 6.3 系统配置

#### 6.3.1 获取配置

##### 请求信息
- **URL**: `/api/v1/config/get`
- **方法**: `GET`
- **查询参数**:
  - `key`: 配置键名（可选，不提供则获取所有配置）

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "site_name": "个人网站",
    "site_description": "description",
    "contact_email": "email@example.com",
    "social_links": {
      "github": "https://github.com",
      "twitter": "https://twitter.com"
    }
  }
}
```

#### 6.3.2 更新配置

##### 请求信息
- **URL**: `/api/v1/config/update`
- **方法**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **请求体**:
```json
{
  "key": "string",
  "value": "string",
  "type": "string" (可选，string/json/number/boolean)
}
```

##### 响应信息
- **成功**:
```json
{
  "code": 200,
  "message": "配置更新成功",
  "data": null
}
```

## 7. 权限说明

| 接口模块 | 公开访问 | 会员访问 | 管理员访问 |
| :--- | :--- | :--- | :--- |
| 用户认证 | 部分(注册/登录) | 全部 | 全部 |
| 博客文章 | 列表/详情 | 列表/详情 | 全部(增删改查) |
| 作品集 | 列表/详情 | 列表/详情 | 全部(增删改查) |
| 笔记 | 只读 | 只读 | 全部(增删改查) |
| 评论 | 列表(仅已审核) | 列表(仅已审核)/发表 | 全部(审核/删除/查看待审核) |
| 评论审核 | 否 | 否 | 是(待审核列表/审核/拒绝/批量操作) |
| RSS订阅 | 全部 | 全部 | 全部 |
| 联系表单 | 提交 | 提交 | 管理 |
| 脑图管理 | 只读 | 只读 | 全部(增删改查) |
| 系统服务 | 部分(验证码) | 部分 | 全部 |
| 仪表板 | 否 | 否 | 是 |
| 系统配置 | 否 | 否 | 是 |

## 8. TypeScript类型定义

### 8.1 响应类型

```typescript
interface ApiResponse<T = any> {
  code: number;
  message: string;
  data: T | null;
}

interface PaginatedResponse<T> {
  total: number;
  page: number;
  limit: number;
  items: T[];
}
```

### 8.2 用户相关类型

```typescript
interface UserInfo {
  id: number;
  username: string;
  email: string;
  nickname?: string;
  avatar?: string;
  bio?: string;
  role: string;
  created_at: string;
}

interface LoginResponse {
  user_id: number;
  username: string;
  email: string;
  role: string;
  token: string;
}
```

### 8.3 博客相关类型

```typescript
interface BlogListItem {
  id: number;
  title: string;
  summary: string;
  cover_image?: string;
  category_id: number;
  category_name: string;
  author_id: number;
  author_name: string;
  view_count: number;
  comment_count: number;
  status: string;
  published_at: string;
  created_at: string;
  tags: string[];
}

interface BlogDetail extends BlogListItem {
  content: string;
  updated_at: string;
  tags: { id: number; name: string }[];
}
```

## 9. 附录

### 9.1 分页参数说明

所有列表查询接口均支持统一的分页参数：
- `page`: 页码，从1开始
- `limit`: 每页数量，最大不超过100

### 9.2 排序参数说明

支持排序的接口可使用以下参数：
- `order_by`: 排序字段
- `order`: 排序方向，可选值：`asc`（升序）、`desc`（降序）

### 9.3 限流说明

- 公开接口：60次/分钟/IP
- 认证接口：30次/分钟/用户
- 敏感操作（如登录、注册）：5次/分钟/IP

### 9.4 缓存策略

- 博客列表：5分钟
- 博客详情：10分钟
- 作品列表：5分钟
- 脑图数据：15分钟
- 系统配置：30分钟