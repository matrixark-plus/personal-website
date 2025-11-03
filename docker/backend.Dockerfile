FROM php:8.1-cli

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# 清理缓存
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 安装PHP扩展
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 安装Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /app

# 暴露端口
EXPOSE 9501

# 启动应用
CMD ["php", "./bin/hyperf.php", "start"]