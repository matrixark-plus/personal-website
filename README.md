# 个人网站项目

## 项目概述
本项目是一个现代化的个人网站，使用前后端分离架构，包含博客、笔记、作品集等核心功能模块。

## 技术架构

### 前端技术栈
- **框架**：Next.js 14
- **语言**：JavaScript/TypeScript
- **状态管理**：React Context/SWR
- **UI组件库**：（根据需要选择）

### 后端技术栈
- **框架**：Hyperf（基于Swoole的协程框架）
- **语言**：PHP 8.1+
- **数据库**：MySQL 8.0
- **缓存**：Redis 7
- **API规范**：RESTful API

## 本地开发环境

### 混合开发环境设置

#### 开发环境组成
- **后端服务**：Docker容器运行（Hyperf）
- **数据库服务**：Docker容器运行（MySQL）
- **缓存服务**：Docker容器运行（Redis）
- **后台管理系统**：Docker容器运行（Hyperf-admin）
- **前端服务**：本地直接运行（Next.js）

#### 前置要求
- Docker 和 Docker Compose
- Node.js 16+
- PHP 8.1+（本地安装可选，主要使用容器内的PHP）

### 环境设置步骤

#### 1. 克隆项目
```bash
git clone <项目仓库地址>
cd mysite
```

#### 2. 后端环境设置
```bash
# 进入后端目录
cd backend

# 启动Docker容器服务（包括后端API和后台管理系统）
docker-compose up -d

# 安装PHP依赖（如果需要本地开发）
composer install
```

#### 3. 前端环境设置
```bash
# 返回项目根目录
cd ..

# 进入前端目录
cd frontend

# 安装Node.js依赖
npm install

# 启动本地开发服务器
npm run dev
```

#### 4. 访问应用
- 前端：http://localhost:3000
- 后端API：http://localhost:9501
- 后台管理系统：http://localhost:9502

### 开发注意事项
- 前端代码修改会自动刷新（本地开发服务器支持热重载）
- 后端代码修改会在容器内自动重启（Hyperf热重载）
- 后台管理系统代码修改会在容器内自动重启（Hyperf热重载）
- 数据库变更需创建迁移文件并执行

## 项目结构
- `backend/`：Hyperf后端代码
- `frontend/`：Next.js前端代码
- `admin/`：Hyperf后台管理系统代码
- `docs/`：项目文档

## 核心功能模块
- 首页：个人简介、最新博客、精选作品展示
- 博客：文章发布、分类、标签、评论
- 笔记：个人知识管理
- 作品集：项目展示
- 联系：联系表单和信息
- 管理：后台管理系统（Docker容器运行）
- 分析：用户行为分析

## 部署
### 开发环境
混合环境：后端和数据库使用Docker容器，前端本地运行

### 生产环境
ECS单机部署模式，所有服务在同一台服务器上运行