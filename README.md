# 个人网站项目

这是一个现代化的个人网站项目，包含前端用户界面、后端API服务和后台管理系统。

## 项目结构

```
.
├── backend/              # 后端API服务 (Hyperf框架)
├── frontend/             # 前端用户界面 (Next.js)
├── admin/                # 后台管理系统 (Hyperf-admin)
├── docker/               # Docker配置文件
└── docs/                 # 项目文档
```

## 技术栈

- **前端用户界面**: Next.js 14 (App Router)
- **后台管理系统**: Hyperf框架
- **后端API服务**: Hyperf框架
- **数据库**: MySQL 8.0
- **缓存**: Redis 7
- **部署**: Docker + Docker Compose

## 本地开发环境搭建

### 环境要求

- Docker
- Docker Compose

### 启动开发环境

```bash
# 进入docker目录
cd docker

# 启动所有服务
docker-compose up -d
```

服务启动后可以通过以下地址访问：

- 前端用户界面: http://localhost:3000
- 后端API服务: http://localhost:9501
- 后台管理系统: http://localhost:9502
- MySQL数据库: localhost:3306
- Redis缓存: localhost:6379

### 停止开发环境

```bash
# 停止所有服务
docker-compose down
```

## 各模块说明

### 后端API服务 (backend/)

基于Hyperf框架构建的RESTful API服务，提供博客、作品、笔记等核心功能的API接口。

### 前端用户界面 (frontend/)

基于Next.js构建的现代化用户界面，包含首页、博客、作品、笔记等页面。

### 后台管理系统 (admin/)

基于Hyperf-admin构建的后台管理系统，用于管理网站内容和用户。

## 开发指南

### 后端开发

1. 进入backend目录
2. 安装PHP依赖: `composer install`
3. 复制环境配置: `cp .env.example .env`
4. 启动开发服务器: `php ./bin/hyperf.php start`

### 前端开发

1. 进入frontend目录
2. 安装Node.js依赖: `npm install`
3. 启动开发服务器: `npm run dev`

### 后台管理开发

1. 进入admin目录
2. 安装PHP依赖: `composer install`
3. 复制环境配置: `cp .env.example .env`
4. 启动开发服务器: `php ./bin/hyperf.php start`