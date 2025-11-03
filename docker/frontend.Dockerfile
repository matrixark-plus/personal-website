FROM node:18-alpine

# 设置工作目录
WORKDIR /app

# 暴露端口
EXPOSE 3000

# 启动应用
CMD ["npm", "run", "dev"]