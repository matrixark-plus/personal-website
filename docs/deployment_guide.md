# 部署指南

## 1. 概述

本文档提供个人网站项目的部署指南，包括环境要求、配置步骤、启动命令等内容。本项目采用Docker容器化部署方案，支持开发环境和生产环境的快速部署。

## 2. 环境要求

### 2.1 开发环境

- **操作系统**：Windows 10/11, macOS, Linux
- **Node.js**：v16.x 或更高版本
- **npm**：v8.x 或更高版本
- **Python**：v3.8 或更高版本
- **Git**：任意最新稳定版本
- **Docker**：v20.x 或更高版本
- **Docker Compose**：v2.x 或更高版本
- **MySQL**：v8.0 或更高版本（可选，如果不使用Docker）
- **Redis**：v6.0 或更高版本（可选，如果不使用Docker）

### 2.2 生产环境

- **操作系统**：Linux (推荐Ubuntu 20.04 LTS或CentOS 8)
- **Docker**：v20.x 或更高版本
- **Docker Compose**：v2.x 或更高版本
- **CPU**：至少2核
- **内存**：至少4GB RAM
- **存储**：至少20GB SSD
- **网络**：固定公网IP，开放80/443端口

## 3. 项目结构

```
mysite/
├── frontend/           # 前端代码目录
│   ├── public/         # 静态资源
│   ├── src/            # 源代码
│   ├── package.json    # 项目依赖配置
│   └── Dockerfile      # 前端Docker配置
├── backend/            # 后端代码目录
│   ├── app/            # 应用代码
│   ├── config/         # 配置文件
│   ├── requirements.txt # Python依赖
│   └── Dockerfile      # 后端Docker配置
├── docs/               # 文档目录
├── .env.example        # 环境变量示例
├── docker-compose.yml  # Docker Compose配置
├── nginx.conf          # Nginx配置
└── README.md           # 项目说明
```

## 4. 开发环境部署

### 4.1 克隆项目代码

```bash
git clone [项目仓库地址]
cd mysite
```

### 4.2 配置环境变量

复制环境变量示例文件并根据实际情况修改：

```bash
cp .env.example .env
# 编辑.env文件，设置数据库连接、密钥等配置
```

### 4.3 使用Docker Compose启动开发环境

```bash
docker-compose up -d
```

该命令会启动以下服务：
- 前端开发服务器（默认端口：3000）
- 后端API服务器（默认端口：8000）
- MySQL数据库（默认端口：3306）
- Redis缓存（默认端口：6379）

### 4.4 数据库初始化

进入后端容器并执行数据库迁移：

```bash
docker exec -it mysite-backend bash
python manage.py migrate
python manage.py createsuperuser
```

### 4.5 访问开发环境

- 前端：http://localhost:3000
- 后端API文档：http://localhost:8000/api/docs
- 管理员后台：http://localhost:8000/admin

## 5. 生产环境部署

### 5.1 准备工作

1. 确保服务器已安装Docker和Docker Compose
2. 配置域名DNS解析到服务器IP
3. 准备SSL证书（可选，推荐使用Let's Encrypt）

### 5.2 配置环境变量

创建并编辑生产环境的环境变量文件：

```bash
cp .env.example .env.production
# 编辑.env.production文件，设置生产环境配置
# 注意：生产环境应使用更安全的密钥和密码
```

### 5.3 构建Docker镜像

```bash
docker-compose -f docker-compose.prod.yml build
```

### 5.4 启动生产环境服务

```bash
docker-compose -f docker-compose.prod.yml up -d
```

该命令会启动以下服务：
- 前端静态文件服务（Nginx）
- 后端API服务
- MySQL数据库
- Redis缓存
- Nginx反向代理（处理HTTP/HTTPS请求）

### 5.5 Nginx配置

生产环境使用Nginx作为反向代理，配置示例：

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name yourdomain.com;
    
    ssl_certificate /etc/nginx/ssl/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers 'ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    
    location / {
        root /usr/share/nginx/html;
        try_files $uri $uri/ /index.html;
    }
    
    location /api/ {
        proxy_pass http://backend:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    location /static/ {
        alias /app/static/;
        expires 30d;
    }
    
    location /media/ {
        alias /app/media/;
        expires 30d;
    }
}
```

### 5.6 数据库优化

生产环境建议进行以下数据库优化：

1. 创建适当的索引
2. 配置合理的缓存
3. 设置定期备份

### 5.7 性能优化

1. 启用Gzip压缩
2. 配置浏览器缓存
3. 启用CDN加速静态资源
4. 配置负载均衡（可选，多服务器部署时）

## 6. 容器编排配置

### 6.1 Docker Compose配置示例

```yaml
version: '3.8'

services:
  frontend:
    build: ./frontend
    container_name: mysite-frontend
    ports:
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
      - ./ssl:/etc/nginx/ssl:ro
    depends_on:
      - backend
    restart: always
    
  backend:
    build: ./backend
    container_name: mysite-backend
    environment:
      - DJANGO_SETTINGS_MODULE=mysite.settings.production
      - DATABASE_URL=mysql://admin:password@db:3306/example_db
      - REDIS_URL=redis://redis:6379/0
    volumes:
      - ./backend:/app
      - static_volume:/app/static
      - media_volume:/app/media
    depends_on:
      - db
      - redis
    restart: always
    
  db:
    image: mysql:8.0
    container_name: mysite-db
    environment:
      - MYSQL_DATABASE=example_db
      - MYSQL_USER=admin
      - MYSQL_PASSWORD=password
      - MYSQL_ROOT_PASSWORD=root_password
    volumes:
      - db_volume:/var/lib/mysql
      - ./mysql-init:/docker-entrypoint-initdb.d
    restart: always
    
  redis:
    image: redis:6.2
    container_name: mysite-redis
    volumes:
      - redis_volume:/data
    restart: always

volumes:
  db_volume:
  redis_volume:
  static_volume:
  media_volume:
```

### 6.2 生产环境Docker Compose配置

```yaml
version: '3.8'

services:
  nginx:
    image: nginx:latest
    container_name: mysite-nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
      - ./ssl:/etc/nginx/ssl:ro
      - static_volume:/usr/share/nginx/html/static
      - media_volume:/usr/share/nginx/html/media
    depends_on:
      - backend
    restart: always
    
  backend:
    build: 
      context: ./backend
      dockerfile: Dockerfile.prod
    container_name: mysite-backend
    environment:
      - DJANGO_SETTINGS_MODULE=mysite.settings.production
      - DATABASE_URL=mysql://admin:password@db:3306/example_db
      - REDIS_URL=redis://redis:6379/0
      - SECRET_KEY=your_secure_secret_key
      - ALLOWED_HOSTS=yourdomain.com
    volumes:
      - static_volume:/app/static
      - media_volume:/app/media
    depends_on:
      - db
      - redis
    restart: always
    
  db:
    image: mysql:8.0
    container_name: mysite-db
    environment:
      - MYSQL_DATABASE=example_db
      - MYSQL_USER=admin
      - MYSQL_PASSWORD=password
      - MYSQL_ROOT_PASSWORD=root_password
    volumes:
      - db_volume:/var/lib/mysql
    restart: always
    
  redis:
    image: redis:6.2
    container_name: mysite-redis
    volumes:
      - redis_volume:/data
    restart: always

volumes:
  db_volume:
  redis_volume:
  static_volume:
  media_volume:
```

## 7. 服务管理

### 7.1 启动服务

```bash
# 开发环境
docker-compose up -d

# 生产环境
docker-compose -f docker-compose.prod.yml up -d
```

### 7.2 停止服务

```bash
# 开发环境
docker-compose down

# 生产环境
docker-compose -f docker-compose.prod.yml down
```

### 7.3 查看服务状态

```bash
# 开发环境
docker-compose ps

# 生产环境
docker-compose -f docker-compose.prod.yml ps
```

### 7.4 查看服务日志

```bash
# 查看所有服务日志
docker-compose logs

# 查看特定服务日志
docker-compose logs backend
```

### 7.5 重启服务

```bash
# 重启所有服务
docker-compose restart

# 重启特定服务
docker-compose restart backend
```

## 8. 数据备份与恢复

### 8.1 数据库备份

```bash
# 备份数据库到本地文件
docker exec mysite-db mysqldump -u admin -p example_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 8.2 数据库恢复

```bash
# 从备份文件恢复数据库
cat backup_file.sql | docker exec -i mysite-db mysql -u admin -p example_db
```

### 8.3 自动化备份

创建定时任务进行自动备份：

```bash
# 编辑crontab
crontab -e

# 添加如下定时任务（每天凌晨2点备份）
0 2 * * * docker exec mysite-db mysqldump -u admin -ppassword example_db > /path/to/backup/backup_$(date +\%Y\%m\%d_\%H\%M\%S).sql
```

## 9. 常见问题处理

### 9.1 服务无法启动

1. 检查环境变量配置是否正确
2. 检查端口是否被占用
3. 查看服务日志获取详细错误信息

### 9.2 数据库连接失败

1. 确认数据库服务是否正常运行
2. 检查数据库连接参数是否正确
3. 验证数据库用户权限

### 9.3 静态资源无法访问

1. 检查静态文件是否正确收集
2. 确认Nginx配置中静态文件路径是否正确
3. 检查文件权限

### 9.4 性能优化建议

1. 增加服务器内存和CPU资源
2. 启用数据库查询缓存
3. 优化图片大小和格式
4. 使用CDN加速静态资源

## 10. 安全加固

### 10.1 生产环境安全配置

1. 使用强密码和安全密钥
2. 定期更新软件包和依赖
3. 限制SSH访问
4. 配置防火墙规则
5. 启用HTTPS
6. 定期检查安全日志

### 10.2 Docker安全最佳实践

1. 使用官方或经过验证的基础镜像
2. 最小化镜像大小
3. 避免以root用户运行容器
4. 使用只读文件系统
5. 限制容器资源使用

## 11. 监控与维护

### 11.1 系统监控

推荐使用以下工具进行系统监控：
- Prometheus + Grafana：监控系统资源和应用性能
- ELK Stack：日志收集和分析
- Uptime Robot：网站可用性监控

### 11.2 定期维护任务

1. 更新Docker镜像和容器
2. 清理无用的Docker资源
3. 检查并修复数据库问题
4. 优化数据库索引
5. 清理日志文件

## 12. 扩展阅读

- [Docker官方文档](https://docs.docker.com/)
- [Docker Compose官方文档](https://docs.docker.com/compose/)
- [Nginx官方文档](http://nginx.org/en/docs/)
- [MySQL官方文档](https://dev.mysql.com/doc/)
- [Let's Encrypt文档](https://letsencrypt.org/docs/)