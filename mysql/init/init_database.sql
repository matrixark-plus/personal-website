-- 数据库初始化脚本
-- 根据数据库设计文档创建所有表结构

SET FOREIGN_KEY_CHECKS = 0;

-- 删除现有表（如果存在）
DROP TABLE IF EXISTS `user_analytics`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `contact_forms`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `blog_tag_relations`;
DROP TABLE IF EXISTS `blog_tags`;
DROP TABLE IF EXISTS `blog_categories`;
DROP TABLE IF EXISTS `node_links`;
DROP TABLE IF EXISTS `mindmap_nodes`;
DROP TABLE IF EXISTS `mindmap_roots`;
DROP TABLE IF EXISTS `system_configs`;
DROP TABLE IF EXISTS `note_versions`;
DROP TABLE IF EXISTS `notes`;
DROP TABLE IF EXISTS `works`;
DROP TABLE IF EXISTS `blogs`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. 用户表 (users)
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` VARCHAR(50) NOT NULL COMMENT '用户名',
  `email` VARCHAR(100) NOT NULL COMMENT '邮箱',
  `password_hash` VARCHAR(255) NOT NULL COMMENT '哈希后的密码',
  `role` ENUM('user', 'admin', 'guest') NOT NULL DEFAULT 'user' COMMENT '用户角色',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '用户状态(0禁用/1启用)',
  `nickname` VARCHAR(50) NULL COMMENT '用户昵称',
  `avatar` VARCHAR(255) NULL COMMENT '头像URL',
  `bio` TEXT NULL COMMENT '个人简介',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `last_login_at` DATETIME NULL COMMENT '最后登录时间',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_username` (`username`),
  INDEX `idx_email_password` (`email`, `password_hash`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- 2. 博客分类表 (blog_categories)
CREATE TABLE `blog_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` VARCHAR(50) NOT NULL COMMENT '分类名称',
  `slug` VARCHAR(50) NOT NULL COMMENT '分类别名(URL友好)',
  `parent_id` INT(11) NULL COMMENT '父分类ID，自关联，支持多级分类',
  `description` VARCHAR(255) NULL COMMENT '分类描述',
  `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序顺序',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_name` (`name`),
  UNIQUE INDEX `idx_slug` (`slug`),
  INDEX `idx_parent_sort` (`parent_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='博客分类表';

-- 3. 博客表 (blogs)
CREATE TABLE `blogs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '博客ID',
  `title` VARCHAR(255) NOT NULL COMMENT '博客标题',
  `content` LONGTEXT NOT NULL COMMENT '博客内容(Markdown格式)',
  `summary` VARCHAR(500) NULL COMMENT '博客摘要',
  `cover_image` VARCHAR(255) NULL COMMENT '封面图片URL',
  `category_id` INT(11) NOT NULL COMMENT '分类ID，关联blog_categories表',
  `author_id` INT(11) NOT NULL COMMENT '作者ID，关联users表',
  `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft' COMMENT '状态',
  `view_count` INT(11) NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `comment_count` INT(11) NOT NULL DEFAULT 0 COMMENT '评论次数',
  `published_at` DATETIME NULL COMMENT '发布时间',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_status_created` (`status`, `created_at`),
  INDEX `idx_category_id` (`category_id`),
  INDEX `idx_author_id` (`author_id`),
  FULLTEXT INDEX `idx_fulltext_title_content` (`title`, `content`),
  CONSTRAINT `fk_blogs_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_blogs_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='博客表';

-- 4. 博客标签表 (blog_tags)
CREATE TABLE `blog_tags` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `name` VARCHAR(50) NOT NULL COMMENT '标签名称',
  `slug` VARCHAR(50) NOT NULL COMMENT '标签别名(URL友好)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_name` (`name`),
  UNIQUE INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='博客标签表';

-- 5. 博客标签关联表 (blog_tag_relations)
CREATE TABLE `blog_tag_relations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `blog_id` INT(11) NOT NULL COMMENT '博客ID，关联blogs表',
  `tag_id` INT(11) NOT NULL COMMENT '标签ID，关联blog_tags表',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_blog_tag` (`blog_id`, `tag_id`),
  INDEX `idx_tag_id` (`tag_id`),
  CONSTRAINT `fk_blog_tag_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_blog_tag_tag` FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='博客标签关联表';

-- 6. 作品表 (works)
CREATE TABLE `works` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '作品ID',
  `title` VARCHAR(255) NOT NULL COMMENT '作品标题',
  `description` TEXT NOT NULL COMMENT '作品描述',
  `images` JSON NOT NULL COMMENT '作品图片URL数组',
  `project_link` VARCHAR(255) NULL COMMENT '项目链接',
  `github_link` VARCHAR(255) NULL COMMENT 'GitHub链接',
  `category` VARCHAR(50) NOT NULL COMMENT '作品分类',
  `author_id` INT(11) NOT NULL COMMENT '作者ID，关联users表',
  `is_public` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否公开',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_public_created` (`is_public`, `created_at`),
  INDEX `idx_author_id` (`author_id`),
  CONSTRAINT `fk_works_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='作品表';

-- 7. 笔记表 (notes)
CREATE TABLE `notes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '笔记ID',
  `title` VARCHAR(255) NOT NULL COMMENT '笔记标题',
  `content` LONGTEXT NOT NULL COMMENT '笔记内容(Markdown格式)',
  `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft' COMMENT '状态',
  `is_public` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否公开',
  `creator_id` INT(11) NOT NULL COMMENT '创建者ID，关联users表',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_status_public_created` (`status`, `is_public`, `created_at`),
  INDEX `idx_creator_id` (`creator_id`),
  FULLTEXT INDEX `idx_fulltext_title_content` (`title`, `content`),
  CONSTRAINT `fk_notes_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='笔记表';

-- 8. 笔记版本历史表 (note_versions)
CREATE TABLE `note_versions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '版本ID',
  `note_id` INT(11) NOT NULL COMMENT '笔记ID，关联notes表',
  `content_snapshot` LONGTEXT NOT NULL COMMENT '内容快照',
  `title_snapshot` VARCHAR(255) NOT NULL COMMENT '标题快照',
  `version_number` INT(11) NOT NULL COMMENT '版本号',
  `created_by` INT(11) NOT NULL COMMENT '创建人ID，关联users表',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  INDEX `idx_note_version` (`note_id`, `version_number`),
  INDEX `idx_created_by` (`created_by`),
  CONSTRAINT `fk_note_versions_note` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_note_versions_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='笔记版本历史表';

-- 9. 评论表 (comments)
CREATE TABLE `comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `user_id` INT(11) NOT NULL COMMENT '用户ID，关联users表',
  `post_id` INT(11) NOT NULL COMMENT '关联ID（博客或作品ID）',
  `post_type` ENUM('blog', 'work') NOT NULL COMMENT '关联类型',
  `parent_id` INT(11) NULL COMMENT '父评论ID，用于回复功能',
  `content` TEXT NOT NULL COMMENT '评论内容（限制1000个中文字符）',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '审核状态(0待审核/1已审核通过/2已拒绝)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_post_id_type` (`post_id`, `post_type`),
  INDEX `idx_status_created` (`status`, `created_at`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_parent_id` (`parent_id`),
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评论表';

-- 10. 订阅表 (subscriptions)
CREATE TABLE `subscriptions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '订阅ID',
  `email` VARCHAR(100) NOT NULL COMMENT '订阅邮箱',
  `token` VARCHAR(255) NOT NULL COMMENT '验证令牌',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '订阅状态(0未验证/1已验证/2已取消)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_email` (`email`),
  INDEX `idx_token_status` (`token`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订阅表';

-- 11. 联系表单表 (contact_forms)
CREATE TABLE `contact_forms` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '表单提交ID',
  `name` VARCHAR(100) NOT NULL COMMENT '提交人姓名',
  `email` VARCHAR(100) NOT NULL COMMENT '提交人邮箱',
  `subject` VARCHAR(255) NOT NULL COMMENT '主题',
  `message` TEXT NOT NULL COMMENT '内容',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '处理状态(0未处理/1已处理)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '提交时间',
  `processed_at` DATETIME NULL COMMENT '处理时间',
  `processed_by` INT(11) NULL COMMENT '处理人ID，关联users表',
  PRIMARY KEY (`id`),
  INDEX `idx_status_created` (`status`, `created_at`),
  INDEX `idx_email` (`email`),
  CONSTRAINT `fk_contact_forms_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系表单表';

-- 12. 系统配置表 (system_configs)
CREATE TABLE `system_configs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `config_key` VARCHAR(100) NOT NULL COMMENT '配置项名称',
  `config_value` TEXT NOT NULL COMMENT '配置值',
  `description` VARCHAR(255) NULL COMMENT '配置项描述',
  `type` VARCHAR(50) NOT NULL DEFAULT 'string' COMMENT '配置类型',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_config_key_value` (`config_key`, `config_value`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';

-- 13. 脑图根节点表 (mindmap_roots)
CREATE TABLE `mindmap_roots` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '根节点ID',
  `title` VARCHAR(255) NOT NULL COMMENT '根节点标题',
  `description` TEXT NULL COMMENT '描述',
  `screenshot_path` VARCHAR(255) NULL COMMENT '脑图截图路径',
  `creator_id` INT(11) NOT NULL COMMENT '创建者ID，关联users表',
  `is_public` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否公开',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_public_created` (`is_public`, `created_at`),
  INDEX `idx_creator_created` (`creator_id`, `created_at`),
  INDEX `idx_title` (`title`),
  CONSTRAINT `fk_mindmap_roots_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='脑图根节点表';

-- 14. 脑图节点表 (mindmap_nodes)
CREATE TABLE `mindmap_nodes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '节点ID',
  `root_id` INT(11) NOT NULL COMMENT '根节点ID，关联mindmap_roots表',
  `parent_id` INT(11) NULL COMMENT '父节点ID，自关联',
  `title` VARCHAR(255) NOT NULL COMMENT '节点标题',
  `node_type` ENUM('node', 'note_link') NOT NULL COMMENT '节点类型',
  `note_id` INT(11) NULL COMMENT '关联笔记ID（仅node_type=note_link时有效）',
  `position_x` FLOAT NULL COMMENT '节点X坐标',
  `position_y` FLOAT NULL COMMENT '节点Y坐标',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_root_parent` (`root_id`, `parent_id`),
  INDEX `idx_node_type_note` (`node_type`, `note_id`),
  CONSTRAINT `fk_mindmap_nodes_root` FOREIGN KEY (`root_id`) REFERENCES `mindmap_roots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mindmap_nodes_parent` FOREIGN KEY (`parent_id`) REFERENCES `mindmap_nodes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mindmap_nodes_note` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='脑图节点表';

-- 15. 节点链接表 (node_links)
CREATE TABLE `node_links` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '链接ID',
  `source_node_id` INT(11) NOT NULL COMMENT '源节点ID，关联mindmap_nodes表',
  `target_node_id` INT(11) NOT NULL COMMENT '目标节点ID，关联mindmap_nodes表',
  `link_type` VARCHAR(50) NOT NULL DEFAULT 'bidirectional' COMMENT '链接类型',
  `label` VARCHAR(100) NULL COMMENT '链接标签',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  INDEX `idx_source_node` (`source_node_id`),
  INDEX `idx_target_node` (`target_node_id`),
  INDEX `idx_link_type` (`link_type`),
  INDEX `idx_source_target` (`source_node_id`, `target_node_id`),
  INDEX `idx_target_source` (`target_node_id`, `source_node_id`),
  CONSTRAINT `fk_node_links_source` FOREIGN KEY (`source_node_id`) REFERENCES `mindmap_nodes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_node_links_target` FOREIGN KEY (`target_node_id`) REFERENCES `mindmap_nodes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='节点链接表';

-- 16. 用户分析表 (user_analytics)
CREATE TABLE `user_analytics` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `user_id` INT(11) NULL COMMENT '用户ID（可为空，访客）',
  `session_id` VARCHAR(255) NOT NULL COMMENT '会话ID',
  `event_type` VARCHAR(50) NOT NULL COMMENT '事件类型',
  `event_data` JSON NULL COMMENT '事件详细数据',
  `url` VARCHAR(500) NOT NULL COMMENT '访问URL',
  `referrer` VARCHAR(500) NULL COMMENT '来源URL',
  `user_agent` VARCHAR(255) NOT NULL COMMENT '用户代理',
  `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP地址',
  `browser` VARCHAR(50) NULL COMMENT '浏览器',
  `device` VARCHAR(50) NULL COMMENT '设备类型',
  `os` VARCHAR(50) NULL COMMENT '操作系统',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  INDEX `idx_session_created` (`session_id`, `created_at`),
  INDEX `idx_event_created` (`event_type`, `created_at`),
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户分析表';

-- 插入默认管理员用户
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`, `status`, `nickname`) 
VALUES ('admin', 'admin@example.com', '$2y$10$8V94Z9uA0lqDnQ6XK5qFpO8w4eZ7r7z5v9vQ3c7y5f2e8d9c1x0a9', 'admin', 1, '管理员');

-- 插入默认配置项
INSERT INTO `system_configs` (`config_key`, `config_value`, `description`, `type`)
VALUES 
('site_name', '个人网站', '网站名称', 'string'),
('site_description', '我的个人作品集和博客', '网站描述', 'string'),
('comment_review_enabled', '1', '是否启用评论审核', 'boolean'),
('max_comment_length', '1000', '最大评论长度', 'number'),
('pagination_size', '10', '分页大小', 'number');

-- 插入默认博客分类
INSERT INTO `blog_categories` (`name`, `slug`, `description`, `sort_order`)
VALUES 
('技术', 'tech', '技术相关文章', 1),
('生活', 'life', '生活随笔', 2),
('分享', 'share', '经验分享', 3);

-- 插入默认博客标签
INSERT INTO `blog_tags` (`name`, `slug`)
VALUES 
('PHP', 'php'),
('MySQL', 'mysql'),
('JavaScript', 'javascript'),
('Vue', 'vue'),
('React', 'react');

-- 脚本执行完成提示
SELECT '数据库初始化完成！所有表已创建，默认数据已插入。' AS message;